<?php
namespace App\Repositories;

use App\Constants\Base;
use App\Contracts\Repositories\DepositRepository as DepositRepositoryInterface;
use App\Models\CryptoAddress;
use App\Models\CryptoTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Etherscan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositRepository implements DepositRepositoryInterface
{
    public function deposit($txhash, $currentUser = null)
    {
        $tx = null;
        if (is_array($txhash)) {
            $tx     = $txhash;
            $txhash = $tx['hash'];
        }

        DB::beginTransaction();

        $transaction = CryptoTransaction::whereRaw('LOWER(transaction_hash) = "' . strtolower($txhash) . '"')
            ->where('transaction_type', 'deposit')
            ->first();
        if ($transaction) {
            DB::commit();
            return [null, 'Duplicated Transaction Hash'];
        }

        $depositAddress = Wallet::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            });
        })->value('address');

        if (! $tx) {
            ini_set('max_execution_time', 300);

            try {
                $resData = Etherscan::transactionListTokenTransferByAddress(
                    address: $depositAddress,
                    contractAddress: Base::TOKEN_CONTRACT_ADDRESSES['USDC'],
                    page: 1,
                    offset: 100,
                    sort: 'desc'
                );
                $txlist = $resData['result'];
                // Log::debug($txlist);
                $tx = collect($txlist)
                    ->filter(fn($item) =>
                        $item['hash'] === $txhash &&
                        strtolower($item['to']) === strtolower($depositAddress) &&
                        $item['value'] > 0
                    )
                    ->first();
            } catch (\Throwable $th) {
                DB::commit();
                Log::error($th);
                return [null, 'Internal Server Error'];
            }

            if (! $tx) {
                DB::commit();
                return [null, 'Invalid Transaction Hash'];
            }
        }

        $cryptoAddress = CryptoAddress::whereRaw('LOWER(address) = "' . strtolower($tx['from']) . '"')
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $cryptoAddress?->user) {
            DB::commit();
            return [null, 'User Not Found'];
        }

        if ($currentUser && $cryptoAddress->user->id !== $currentUser->id) {
            DB::commit();
            return [null, 'Malicious Request'];
        }

        $amount = bcdiv($tx['value'], '1' . str_repeat('0', 6), 2);

        if (! $cryptoAddress->user->wallet) {
            $cryptoAddress->user->wallet()->create([
                'balance' => 0,
                'status'  => 'active',
            ]);
            $cryptoAddress->user->load('wallet');
        }
        $cryptoAddress->user->wallet->increment('balance', $amount);

        $transaction = CryptoTransaction::create([
            'user_id'          => $cryptoAddress->user->id,
            'amount'           => $amount,
            'description'      => 'Deposit',
            'address_from'     => $tx['from'],
            'transaction_hash' => $txhash,
            'address_to'       => $depositAddress,
            'transaction_type' => 'deposit',
        ]);
        $transaction = $transaction->toArray();

        $cryptoAddress->delete();

        DB::commit();

        return [$transaction, null];
    }
}
