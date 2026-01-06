<?php
namespace App\Repositories;

use App\Contracts\Repositories\CryptoAddressRepository as CryptoAddressRepositoryInterface;
use App\Models\CryptoAddress;

class CryptoAddressRepository extends Repository implements CryptoAddressRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(
            model: CryptoAddress::class,
            validator: \App\Validators\Model\CryptoAddress::class,
        );
    }
}
