<?php
namespace App\Repositories;

use App\Contracts\Repositories\TicketRepository as TicketRepositoryInterface;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TicketRepository extends Repository implements TicketRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(
            model: Ticket::class,
            validator: \App\Validators\Model\Ticket::class,
        );
    }

    public function getStats()
    {
        $stats = [
            'count'  => [],
            'amount' => [],
        ];

        $query = Ticket::whereHas('domain', function ($query) {
            $query->whereHas('client', function ($query) {
                $query->whereNotIn('id', explode(',', env('TEST_USER_IDS')));
            });
        });

        $stats['count']['total']      = $query->clone()->count();
        $stats['count']['pending']    = $query->clone()->where('status', 'pending')->count();
        $stats['count']['sent']       = $query->clone()->where('status', 'sent')->count();
        $stats['count']['validated']  = $query->clone()->where('status', 'validated')->count();
        $stats['count']['processing'] = $query->clone()->where('status', 'processing')->count();
        $stats['count']['completed']  = $query->clone()->where('status', 'completed')->count();
        $stats['count']['reported']   = $query->clone()->where('status', 'reported')->count();
        $stats['count']['declined']   = $query->clone()->where('status', 'declined')->count();
        $stats['count']['error']      = $query->clone()->where('status', 'error')->count();

        // $stats['amount']['total']   = $query->clone()->sum('amount');
        $stats['amount']['validated']  = $query->clone()->where('status', 'validated')->sum('amount');
        $stats['amount']['processing'] = $query->clone()->where('status', 'processing')->sum('amount');
        $stats['amount']['completed']  = $query->clone()->where('status', 'completed')->sum('amount');
        $stats['amount']['avg']        = round($query->clone()->avg('amount'), 2);

        // $stats['fee'] = $query->clone()->where('status', 'completed')
        //     ->selectRaw('ROUND(SUM(0.01 * amount + 0.2), 2) as fee')
        //     ->value('fee');
        // $stats['amount']['total']   = $query->clone()->sum('amount');

        return $stats;
    }

    public function getDailyTotalAmount($searchParams)
    {
        $appTZOffset = Carbon::now(config('app.timezone'))->format('P');

        $data = DB::table('crypto_transactions')
            ->leftJoin('tickets', 'crypto_transactions.transaction_hash', '=', 'tickets.ticket_id')
            ->leftJoin('form_domains', 'tickets.domain_id', '=', 'form_domains.id')
            ->select(
                DB::raw('DATE(CONVERT_TZ(crypto_transactions.created_at, "' . $appTZOffset . '", "' . $searchParams['timezone'] . '")) as date'),
                DB::raw('SUM(tickets.amount) as total_amount')
            )
            ->where(function ($query) use ($searchParams, $appTZOffset) {
                if (isset($searchParams['user_id'])) {
                    $user = User::find($searchParams['user_id']);
                    if ($user) {
                        if ($user->role === 'user') {
                            $query->where('form_domains.client_id', $user->id);
                        }
                    }
                }
                if (isset($searchParams['start_time'])) {
                    $startTime = Carbon::createFromFormat('H:i:s', $searchParams['start_time'])
                        ->timezone($appTZOffset)
                        ->format('H:i:s');
                    $query->whereRaw('TIME(CONVERT_TZ(crypto_transactions.created_at, "' . $appTZOffset . '", "' . $searchParams['timezone'] . '")) >= "' . $startTime . '"');
                }
                if (isset($searchParams['end_time'])) {
                    $endTime = Carbon::createFromFormat('H:i:s', $searchParams['end_time'])
                        ->timezone($appTZOffset)
                        ->format('H:i:s');
                    $query->whereRaw('TIME(CONVERT_TZ(crypto_transactions.created_at, "' . $appTZOffset . '", "' . $searchParams['timezone'] . '")) <= "' . $endTime . '"');
                }
            })
            ->where(function ($query) {
                $query->where('crypto_transactions.description', 'Ticket validation debit')
                    ->orWhere('crypto_transactions.description', 'Ticket validated');
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $data;
    }
}
