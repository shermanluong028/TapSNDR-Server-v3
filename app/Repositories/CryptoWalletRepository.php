<?php
namespace App\Repositories;

use App\Constants\Base;
use App\Contracts\Repositories\CryptoWalletRepository as CryptoWalletRepositoryInterface;
use App\Data\CryptoWallets;
use etherscan\api\Etherscan;

class CryptoWalletRepository implements CryptoWalletRepositoryInterface
{
    public function getData()
    {
        $data = CryptoWallets::all();

        $etherscan = new Etherscan(env('ETHERSCAN_API_KEY'), \App\Constants\Etherscan::API_URL);

        foreach ($data as &$item) {
            $resData        = $etherscan->tokenBalance(Base::TOKEN_CONTRACT_ADDRESSES['USDC'], $item['address']);
            $item['amount'] = bcdiv($resData['result'], bcpow(10, 6), 2);
        }

        return $data;
    }
}
