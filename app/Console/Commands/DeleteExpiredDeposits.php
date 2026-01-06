<?php
namespace App\Console\Commands;

use App\Models\CryptoAddress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteExpiredDeposits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-expired-deposits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = CryptoAddress::where('created_at', '<=', now()->subHour())->delete();

        if ($deleted > 0) {
            Log::info('Deleted ' . $deleted . ' expired deposits');
        }
    }
}
