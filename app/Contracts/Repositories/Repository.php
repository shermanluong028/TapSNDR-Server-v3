<?php
namespace App\Contracts\Repositories;

interface Repository
{
    public function upsert($data, $user);
}
