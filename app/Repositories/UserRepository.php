<?php
namespace App\Repositories;

use App\Contracts\Repositories\UserRepository as UserRepositoryInterface;
use App\Enums\Role;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserRepository extends Repository implements UserRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(
            model: User::class,
            validator: \App\Validators\Model\User::class,
        );
    }

    public function getStatsById($id)
    {
        $user = User::find($id);
        $role = $user->role ?? 'guest';

        $stats = [];

        if ($role === Role::CLIENT->value) {
            $stats['tickets'] = [
                'count'  => [],
                'amount' => [],
                'date'   => [],
            ];

            $query = Ticket::whereHas('domain', function ($query) use ($user) {
                $query->where('client_id', $user->id);
            });

            $stats['tickets']['count']['total'] = $query->clone()->count();
            // $stats['tickets']['count']['completed'] = $query->clone()->where('status', 'completed')->count();
            // $stats['tickets']['count']['declined']  = $query->clone()->where('status', 'declined')->count();

            // $stats['tickets']['amount']['total'] = $query->clone()->sum('amount');
            $stats['tickets']['amount']['completed'] = $query
                ->clone()
                ->where('status', 'completed')
                ->sum('amount');
            // $stats['tickets']['amount']['declined'] = $query->clone()->where('status', 'declined')->sum('amount');
            $stats['tickets']['amount']['avg']['total'] = $query
                ->clone()
                ->selectRaw('ROUND(IFNULL(AVG(amount), 0), 2) as avg_amount')
                ->value('avg_amount');
            $stats['tickets']['amount']['avg']['daily_completed'] = $query
                ->clone()
                ->selectRaw('SUM(tickets.amount) as daily_completed_amount')
                ->where('status', 'completed')
                ->groupByRaw('DATE(tickets.created_at)')
                ->get()
                ->avg('daily_completed_amount');

            $lastTicketAt = $query->clone()->max('created_at');
            if ($lastTicketAt) {
                $stats['tickets']['date']['last'] = Carbon::parse($lastTicketAt)->setTimezone(config('app.timezone'));
            }

            // $stats['tickets']['fee'] = $query
            //     ->clone()
            //     ->where('status', 'completed')
            //     ->selectRaw('ROUND(SUM(0.01 * amount + 0.2), 2) as fee')
            //     ->value('fee');

        } else if ($role === 'fulfiller') {
            $stats['tickets'] = [
                'count' => [
                    'avg' => [
                    ],
                ],
            ];

            $query = $user->tickets();

            // $stats['tickets']['count']['total'] = $query->clone()->count();
            $stats['tickets']['count']['completed']    = $query->clone()->where('status', 'completed')->count();
            $stats['tickets']['count']['reported']     = $query->clone()->where('status', 'reported')->count();
            $stats['tickets']['count']['avg']['1hour'] = (int) $query->clone()
                ->select([
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as completed_hour"),
                    DB::raw('COUNT(*) as count'),
                ])
                ->groupBy('completed_hour')
                ->get()
                ->avg('count');
        }

        return $stats;
    }
}
