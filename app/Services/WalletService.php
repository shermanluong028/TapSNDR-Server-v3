<?php
namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;
use phpseclib\Math\BigInteger;
use Web3p\EthereumTx\Transaction;
use Web3\Contract;
use Web3\Eth;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;

class WalletService
{
    public function withdraw(User $user, $amount, $toAddress, $secretKey)
    {
        if (! $user || ! $user->wallet) {
            Log::channel('daily')->info("The user does not exist or has no wallet.");
            return response('Internal Server Error', 500);
        }
        if ($user->wallet->balance < $amount) {
            Log::channel('daily')->info("Insufficient balance");
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
            Log::channel('daily')->info("No admin wallet");
            return response('Internal Server Error', 500);
        }
        $privateKey = openssl_decrypt(
            base64_decode($wallet->private_key),
            'aes-256-ecb',
            hash('sha256', $secretKey, true),
            OPENSSL_RAW_DATA
        );
        if (! $privateKey) {
            Log::channel('daily')->info("Incorrect secret key");
            return response()->json([
                'status' => 0,
                'error'  => 'Incorrect secret key',
            ], 400);
        }
        $eth   = new Eth(new HttpProvider(new HttpRequestManager(config('web3.RPC_URL'), 30)));
        $nonce = null;
        $eth->getTransactionCount($wallet->address, function ($err, $count) use (&$nonce) {
            if ($err) {
                Log::channel('daily')->info($err);
                return;
            }
            $nonce = $count->toString();

        });
        if (! $nonce) {
            return response('Internal Server Error', 500);
        }

        // Sepolia
        // $contractAddress = "0xbdCED8f4c393929a20356372b8A88a386693F353";
        // Mainnet
        // $contractAddress = "0xdAC17F958D2ee523a2206206994597C13D831ec7";
        $contractAddress = "0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913";
        // $abi             = json_decode(file_get_contents(resource_path('abi/sepolia/usdt.json')), true);
        $abi      = json_decode(file_get_contents(resource_path('abi/mainnet/usdc.json')), true);
        $contract = new Contract($eth->provider, $abi);
        $contract->at($contractAddress);
        $gas = null;
        $contract->estimateGas('transfer', $toAddress, new BigInteger(bcmul($amount, '1' . str_repeat('0', 6))), [
            'from' => $wallet->address,
        ], function ($err, $estimatedGas) use (&$gas) {
            if ($err) {
                Log::channel('daily')->info($err);
                return;
            }
            $gas = $estimatedGas->multiply(new BigInteger(100))->divide(new BigInteger(100))[0]->toString();
        });
        if (! $gas) {
            return response('Internal Server Error', 500);
        }
        // $gasPrice = null;
        // $eth->gasPrice(function ($err, $result) use (&$gasPrice) {
        //     if ($err) {
        //         Log::channel('daily')->info($err);
        //         return;
        //     }
        //     $gasPrice = bcmul($result->toString(), '2');
        // });
        // if (! $gasPrice) {
        //     return response('Internal Server Error', 500);
        // }
        $data        = $contract->getData('transfer', $toAddress, new BigInteger(bcmul($amount, '1' . str_repeat('0', 6))));
        $transaction = new Transaction([
            'nonce'    => '0x' . dechex($nonce),
            'from'     => $wallet->address,
            'to'       => $contractAddress,
            'value'    => '0x0',
            'gas'      => '0x' . dechex($gas),
            'gasPrice' => '0x' . dechex('10000000000'),
            'data'     => '0x' . $data,
            // 'chainId'  => 11155111,
            'chainId'  => 8453,
        ]);
        $signedTransaction = $transaction->sign($privateKey);
        $transactionHash   = null;
        $eth->sendRawTransaction('0x' . $signedTransaction, function ($err, $transaction) use (&$error, &$transactionHash) {
            if ($err) {
                Log::channel('daily')->info($err);
                return;
            }
            $transactionHash = $transaction;
        });
        if (! $transactionHash) {
            return response('Internal Server Error', 500);
        }
        $user->wallet->decrement('balance', $amount);
        return $transactionHash;
    }
}
