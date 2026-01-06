<?php
namespace App\Http\Controllers;

use App\Models\CryptoAddress;

class CryptoAddressesController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: new CryptoAddress,
        );
    }
}
