<?php
namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\CryptoTransaction;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionsController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: new CryptoTransaction,
            validator: new \App\Validators\Model\CryptoTransaction
        );
    }

    public function getStats(Request $request)
    {
        $currentUser = $request->user();

        $conditions = CryptoTransaction::getConditionsForReadableRecords($currentUser);
        $conditions = array_merge($conditions, $this->getAdditionalConditions($request));

        $query = CryptoTransaction::where(function ($query) use ($conditions) {
            Utils::setConditions2Query($query, $conditions);
        });

        $stats = [
            'amount'  => [],
            'tickets' => [
                'amount' => [],
            ],
        ];

        $stats['amount']['total'] = $query
            ->clone()
            ->selectRaw('SUM(CASE WHEN transaction_type = "credit" OR transaction_type = "deposit" THEN amount WHEN transaction_type = "debit" OR transaction_type = "withdraw" THEN -amount ELSE 0 END) AS total_amount')
            ->value('total_amount');

        $stats['tickets']['amount']['total'] = Ticket::whereHas('transactions', function ($query) use ($conditions) {
            Utils::setConditions2Query($query, $conditions);
        })->sum('amount');

        return Utils::responseData($stats);
    }

    protected function getAdditionalConditions(Request $request): array
    {
        $currentUser = $request->user();

        $searchParams = $request->query();

        $conditions = [];

        $conditions[] = [
            ['description', 'LIKE', '%' . ($searchParams['search_key'] ?? '') . '%'],
            ['address_from', 'LIKE', '%' . ($searchParams['search_key'] ?? '') . '%'],
            ['transaction_hash', 'LIKE', '%' . ($searchParams['search_key'] ?? '') . '%'],
            'OR',
        ];

        if (in_array($searchParams['type'] ?? null, ['credit', 'debit', 'deposit', 'withdraw'])) {
            $conditions[] = ['transaction_type', $searchParams['type']];
        }

        if (isset($searchParams['user_id'])) {
            $user = User::find($searchParams['user_id']);
            if ($user) {
                $conditions[] = ['user_id', $searchParams['user_id']];
            }
        }

        $tzOffset = Utils::getTZOffset(config('app.timezone'));

        if (isset($searchParams['start_date'])) {
            $startDate = Carbon::createFromFormat('Y-m-d\TH:i:sP', $searchParams['start_date'])
                ->timezone($tzOffset)
                ->format('Y-m-d H:i:s');
            $conditions[] = ['created_at', '>=', $startDate];
        }

        if (isset($searchParams['end_date'])) {
            $endDate = Carbon::createFromFormat('Y-m-d\TH:i:sP', $searchParams['end_date'])
                ->timezone($tzOffset)
                ->format('Y-m-d H:i:s');
            $conditions[] = ['created_at', '<=', $endDate];
        }

        return $conditions;
    }

    protected function fillData(Request $request, &$data, $operation): void
    {
        if ($operation === 'c') {
            $data['is_manual'] = 1;
        }
    }

    protected function afterUpsert($request, $data, $originalRecord, $updatedRecord): mixed
    {
        if (! $originalRecord) {
            if (! $updatedRecord->user->wallet) {
                $updatedRecord->user->wallet()->create([
                    'balance' => 0,
                    'status'  => 'active',
                ]);
                $updatedRecord->user->load('wallet');
            }
            if (in_array($updatedRecord->transaction_type, ['credit', 'deposit'])) {
                $updatedRecord->user->wallet->increment('balance', $updatedRecord->amount);
            } else {
                $updatedRecord->user->wallet->decrement('balance', $updatedRecord->amount);
            }
        }

        return null;
    }
}
