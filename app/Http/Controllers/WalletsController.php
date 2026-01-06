<?php
namespace App\Http\Controllers;

use App\Contracts\Repositories\WalletRepository;
use App\Helpers\Utils;
use App\Models\Wallet;

class WalletsController extends BaseController
{
    public function __construct(
        WalletRepository $walletRepository
    ) {
        parent::__construct(
            model: new Wallet,
        );
        $this->repositories['wallet'] = $walletRepository;
    }

    public function getStats()
    {
        return Utils::responseData($this->repositories['wallet']->getStats());
    }
}
