<?php
namespace App\Http\Controllers;

use App\Constants\Base;
use App\Models\CryptoTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use phpseclib\Math\BigInteger;
use Telegram\Bot\Laravel\Facades\Telegram;
use Web3p\EthereumTx\Transaction;
use Web3\Contract;
use Web3\Eth;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class WithdrawalsController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: new Withdrawal,
            validator: new \App\Validators\Model\Withdrawal
        );
    }

    public function getStats(Request $request)
    {
        $stats = [
            'count' => [],
        ];

        // $stats['count']['total'] = Withdrawal::count();
        $stats['count']['pending'] = Withdrawal::where('status', 'PENDING')->count();
        // $stats['count']['approved'] = Withdrawal::where('status', 'APPROVE')->count();
        // $stats['count']['declined'] = Withdrawal::where('status', 'REPORT')->count();

        return response()->json([
            'status' => 1,
            'data'   => $stats,
        ]);
    }

    protected function checkUpdatableById(?User $user, $id): bool
    {
        if (! parent::checkUpdatableById($user, $id)) {
            return false;
        }
        $withdrawal = Withdrawal::find($id);
        if (! $withdrawal || $withdrawal->status !== 'PENDING') {
            return false;
        }
        return true;
    }

    protected function fillData(Request $request, &$data, $operation): void
    {
        $currentUser = $request->user();

        if ($operation === 'c') {
            $data['user_id'] = $currentUser->id;
            $data['status']  = 'PENDING';
        }
    }

    protected function afterUpsert(Request $request, $data, $originalRecord, $updatedRecord): mixed
    {
        if (! $originalRecord) {
            if ($updatedRecord->amount > $updatedRecord->user->wallet->balance) {
                return 'Insufficient balance';
            }
            if ($updatedRecord->user->withdrawals()->where('status', 'PENDING')->count() > 1) {
                return 'You have a pending withdrawal request.';
            }
            // if (
            //     $updatedRecord->user->role === 'fulfiller' &&
            //     $updatedRecord->user->tickets()->where('status', 'processing')->count() > 0
            // ) {
            //     return 'You have a processing ticket.';
            // }
        }
        return null;
    }

    protected function completeUpsert(
        Request $request,
        $originalRecord,
        $updatedRecord
    ): mixed {
        if (! $originalRecord) {
            try {
                Telegram::sendMessage([
                    'chat_id' => env('TELEGRAM_CHAT_ID'),
                    'text'    => 'Withdraw request' . (app()->environment('local') ? ' (Test)' : '') . "\nAmount: {$updatedRecord->amount} USDC\nCurrent Balance: {$updatedRecord->user->wallet->balance} USDC\nFulfiller Id: {$updatedRecord->user_id}\nTo: {$updatedRecord->to}",
                ]);
            } catch (\Throwable $th) {
            }
        }
        if (
            $originalRecord &&
            $originalRecord->status === 'PENDING' &&
            $updatedRecord->status === 'APPROVED'
        ) {
            Log::channel('daily')->info("Approve withdrawal #" . $updatedRecord->id);
            $payload   = $request->post();
            $validator = Validator::make($payload, [
                'secret_key' => 'required',
            ]);
            if ($validator->fails()) {
                Log::channel('daily')->error("No secret key");
                return response('Bad Requset', 400);
            }
            $secretKey = $payload['secret_key'];
            $user      = $updatedRecord->user;
            if (! $user || ! $user->wallet) {
                Log::channel('daily')->error("The user does not exist or has no wallet.");
                return response('Internal Server Error', 500);
            }
            if ($user->wallet->balance < $updatedRecord->amount) {
                Log::channel('daily')->error("Insufficient balance");
                return response()->json([
                    'status' => 0,
                    'error'  => 'Insufficient balance',
                ], 400);
            }
            $wallet = Wallet::whereHas('user', function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->where('name', 'admin');
                });
            })->first();
            if (! $wallet) {
                Log::channel('daily')->error("No admin wallet");
                return response('Internal Server Error', 500);
            }
            $privateKey = openssl_decrypt(
                base64_decode($wallet->private_key),
                'aes-256-ecb',
                hash('sha256', $secretKey, true),
                OPENSSL_RAW_DATA
            );
            if (! $privateKey) {
                Log::channel('daily')->error("Incorrect secret key");
                return response()->json([
                    'status' => 0,
                    'error'  => 'Incorrect secret key',
                ], 400);
            }
            $eth   = new Eth(new HttpProvider(new HttpRequestManager(config('web3.RPC_URL'), 30)));
            $nonce = null;
            $eth->getTransactionCount($wallet->address, function ($err, $count) use (&$nonce) {
                if ($err) {
                    Log::error($err);
                    return;
                }
                $nonce = $count->toString();

            });
            if (! $nonce) {
                return response('Internal Server Error', 500);
            }

            $contractAddress = Base::TOKEN_CONTRACT_ADDRESSES['USDC'];
            $abi             = json_decode(file_get_contents(resource_path('abi/mainnet/usdc.json')), true);
            $contract        = new Contract($eth->provider, $abi);
            $contract->at($contractAddress);
            $gas = null;
            $contract->estimateGas('transfer', $updatedRecord->to, new BigInteger(bcmul($updatedRecord->amount, '1' . str_repeat('0', 6))), [
                'from' => $wallet->address,
            ], function ($err, $estimatedGas) use (&$gas) {
                if ($err) {
                    Log::error($err);
                    return;
                }
                $gas = $estimatedGas->multiply(new BigInteger(100))->divide(new BigInteger(100))[0]->toString();
            });
            if (! $gas) {
                return response('Internal Server Error', 500);
            }
            $gasPrice = null;
            $eth->gasPrice(function ($err, $result) use (&$gasPrice) {
                if ($err) {
                    Log::error($err);
                    return;
                }
                $gasPrice = bcmul($result->toString(), '2');
            });
            if (! $gasPrice) {
                return response('Internal Server Error', 500);
            }
            $data        = $contract->getData('transfer', $updatedRecord->to, new BigInteger(bcmul($updatedRecord->amount, '1' . str_repeat('0', 6))));
            $transaction = new Transaction([
                'nonce'    => '0x' . dechex($nonce),
                'from'     => $wallet->address,
                'to'       => $contractAddress,
                'value'    => '0x0',
                'gas'      => '0x' . dechex($gas),
                'gasPrice' => '0x' . dechex($gasPrice),
                'data'     => '0x' . $data,
                'chainId'  => Base::CHAIN_ID,
            ]);
            $signedTransaction = $transaction->sign($privateKey);
            $transactionHash   = null;
            $eth->sendRawTransaction('0x' . $signedTransaction, function ($err, $transaction) use (&$error, &$transactionHash) {
                if ($err) {
                    Log::error($err);
                    return;
                }
                $transactionHash = $transaction;
            });
            if (! $transactionHash) {
                return response('Internal Server Error', 500);
            }
            CryptoTransaction::create([
                'user_id'          => $user->id,
                'amount'           => $updatedRecord->amount,
                'description'      => 'Withdraw',
                'reference_id'     => $updatedRecord->id,
                'wallet_id'        => $user->wallet->id,
                'status'           => 'debit',
                'user_id_from'     => $wallet->user->id,
                'user_id_to'       => $user->id,
                'address_from'     => $wallet->address,
                'transaction_hash' => $transactionHash,
                'address_to'       => $updatedRecord->to,
                'token_type'       => 'USDT',
                'transaction_type' => 'withdraw',
            ]);
            $user->wallet->decrement('balance', $updatedRecord->amount);
        }
        return null;
    }
}
