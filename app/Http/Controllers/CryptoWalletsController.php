<?php
namespace App\Http\Controllers;

use App\Contracts\Repositories\CryptoWalletRepository;
use App\Helpers\Utils;
use Illuminate\Routing\Controller;

class CryptoWalletsController extends Controller
{
    protected CryptoWalletRepository $cryptoWalletRepository;

    public function __construct(
        CryptoWalletRepository $cryptoWalletRepository
    ) {
        $this->cryptoWalletRepository = $cryptoWalletRepository;
    }

    public function get()
    {
        $data = $this->cryptoWalletRepository->getData();
        return Utils::responseData($data);
    }
}
