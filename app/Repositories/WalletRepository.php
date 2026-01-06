<?php
namespace App\Repositories;

use App\Contracts\Repositories\WalletRepository as WalletRepositoryInterface;
use App\Models\Wallet;

class WalletRepository extends Repository implements WalletRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(
            model: Wallet::class,
            validator: \App\Validators\Model\Wallet::class,
        );
    }

    public function getStats()
    {
        $stats = [
            'balance' => [],
        ];

        $stats['balance']['total'] = Wallet::whereNotIn('user_id', explode(',', env('TEST_USER_IDS')))
            ->where('status', 'active')
            ->sum('balance');

        return $stats;
    }
}
