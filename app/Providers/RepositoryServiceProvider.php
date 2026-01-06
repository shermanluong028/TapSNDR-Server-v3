<?php
namespace App\Providers;

use App\Contracts\Repositories\CommissionPercentageRepository as CommissionPercentageRepositoryInterface;
use App\Contracts\Repositories\CryptoAddressRepository as CryptoAddressRepositoryInterface;
use App\Contracts\Repositories\CryptoWalletRepository as CryptoWalletRepositoryInterface;
use App\Contracts\Repositories\DepositRepository as DepositRepositoryInterface;
use App\Contracts\Repositories\TicketRepository as TicketRepositoryInterface;
use App\Contracts\Repositories\UserRepository as UserRepositoryInterface;
use App\Contracts\Repositories\WalletRepository as WalletRepositoryInterface;
use App\Repositories\CommissionPercentageRepository;
use App\Repositories\CryptoAddressRepository;
use App\Repositories\CryptoWalletRepository;
use App\Repositories\DepositRepository;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(CryptoAddressRepositoryInterface::class, CryptoAddressRepository::class);
        $this->app->bind(CommissionPercentageRepositoryInterface::class, CommissionPercentageRepository::class);
        $this->app->bind(CryptoWalletRepositoryInterface::class, CryptoWalletRepository::class);
        $this->app->bind(DepositRepositoryInterface::class, DepositRepository::class);
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
