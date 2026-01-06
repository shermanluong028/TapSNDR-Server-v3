<?php
namespace App\Contracts\Repositories;

interface WalletRepository extends Repository
{
    public function getStats();
}
