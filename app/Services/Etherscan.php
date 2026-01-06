<?php
namespace App\Services;

use etherscan\api\tools\Request;

class Etherscan
{
    public static function transactionListTokenTransferByAddress($address, $contractAddress, $startBlock = 0, $endBlock = 99999999, $sort = "asc", $page = null, $offset = null)
    {
        $request = new Request(env('ETHERSCAN_API_KEY'), \App\Constants\Etherscan::API_URL);
        return $request->exec([
            'module'          => 'account',
            'action'          => 'tokentx',
            'contractaddress' => $contractAddress,
            'address'         => $address,
            'page'            => $page,
            'offset'          => $offset,
            'startblock'      => $startBlock,
            'endblock'        => $endBlock,
            'sort'            => $sort,
        ]);
    }
}
