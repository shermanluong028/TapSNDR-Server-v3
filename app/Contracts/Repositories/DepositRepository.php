<?php
namespace App\Contracts\Repositories;

interface DepositRepository
{
    public function deposit($txhash, $currentUser = null);
}
