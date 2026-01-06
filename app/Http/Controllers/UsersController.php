<?php
namespace App\Http\Controllers;

use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WalletRepository;
use App\Helpers\Utils;
use App\Models\FormDomain;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Wallet;
use App\Repositories\CommissionPercentageRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends BaseController
{
    public function __construct(
        UserRepository $userRepository,
        WalletRepository $walletRepository,
        CommissionPercentageRepository $commissionPercentageRepository
    ) {
        parent::__construct(
            model: new User,
            validator: new \App\Validators\Model\User,
        );
        $this->repositories['user']                  = $userRepository;
        $this->repositories['wallet']                = $walletRepository;
        $this->repositories['commission_percentage'] = $commissionPercentageRepository;
    }

    public function getStats(Request $request)
    {
        $stats = [
            'count' => [],
        ];

        $stats['count']['total'] = User::whereHas('roles', function ($query) {
            $query->whereNot('name', 'player');
        })->count();
        $stats['count']['distributors'] = User::whereHas('roles', function ($query) {
            $query->where('name', 'distributor');
        })->count();
        $stats['count']['fulfillers'] = User::whereHas('roles', function ($query) {
            $query->where('name', 'fulfiller');
        })->count();
        $stats['count']['clients'] = User::whereHas('roles', function ($query) {
            $query->where('name', 'user');
        })->count();

        return response()->json([
            'status' => 1,
            'data'   => $stats,
        ]);
    }

    public function getDomainsById(Request $request, $id)
    {
        return $this->getRelatedDataById_v2(
            $request,
            $id,
            'domains',
            'players',
        );
    }

    public function getTicketsById(Request $request, $id)
    {
        return $this->getRelatedDataById(
            $request,
            $id,
            Ticket::class,
            'user_id',
        );
    }

    public function getStatsById(Request $request, $id)
    {
        $error = $this->validateId($id);
        if ($error) {
            return response()->json([
                'status' => 0,
                'error'  => $error,
            ]);
        }

        $stats = $this->repositories['user']->getStatsById($id);

        return response()->json([
            'status' => 1,
            'data'   => $stats,
        ]);
    }

    public function getWalletById(Request $request, $id)
    {
        $currentUser = $request->user();

        if (! $this->checkReadableById($currentUser, $id)) {
            return response('Forbidden', 403);
        }

        $user = User::find($id);

        if ($user->roles[0]?->name === 'master_admin') {
            $allowedFields = Wallet::getAllowedFields($request->user()->role ?? 'guest', 'r');
            $wallet        = Wallet::select($allowedFields)
                ->whereHas('user', function ($query) {
                    $query->whereHas('roles', function ($query) {
                        $query->where('name', 'admin');
                    });
                })
                ->first();
            return response()->json([
                'status' => 1,
                'data'   => $wallet,
            ]);
        } else {
            // TODO: No need to check if the row with [id] can be read in [getRelatedDataById] function
            return $this->getRelatedDataById(
                $request,
                $id,
                Wallet::class,
                'user_id',
                true
            );
        }
    }

    // public function getClientsById(Request $request, $id)
    // {
    //     return $this->getRelatedDataById(
    //         $request,
    //         $id,
    //         User::class,
    //         'distributor_id',
    //     );
    // }

    public function getCryptoAddressesById(Request $request, $id)
    {
        return $this->getRelatedDataById_v2(
            $request,
            $id,
            'crypto_addresses',
            'user',
        );
    }

    public function attachDomain(Request $request, $id)
    {
        $currentUser = $request->user();

        $payload = $request->post();

        $validator = Validator::make($payload, [
            'vendor_code' => 'required',
        ]);
        if ($validator->fails()) {
            return Utils::responseError($validator->errors()->first());
        }

        if (! $this->checkUpdatableById($currentUser, $id)) {
            return response('Forbidden', 403);
        }

        $user = User::find($id);

        $domain = FormDomain::where('vendor_code', $payload['vendor_code'])->first();
        if (! $domain) {
            return Utils::responseError('Invalid Vendor Code');
        } else if (! $domain->active) {
            return Utils::responseError('Blocked Vendor');
        }

        $user->domains()->syncWithoutDetaching($domain->id);

        return Utils::responseData();
    }

    public function dettachDomain(Request $request, $id)
    {
        $currentUser = $request->user();

        $payload = $request->post();

        $validator = Validator::make($payload, [
            'domain_id' => 'required',
        ]);
        if ($validator->fails()) {
            return Utils::responseError($validator->errors()->first());
        }

        if (! $this->checkUpdatableById($currentUser, $id)) {
            return response('Forbidden', 403);
        }

        $user = User::find($id);

        $domain = $user->domains()->find($payload['domain_id']);
        if (! $domain) {
            return Utils::responseError('Invalid Vendor');
        }

        $user->domains()->detach($domain->id);

        return Utils::responseData();
    }

    protected function getAdditionalConditions(Request $request): array
    {
        $searchParams = $request->query();

        $conditions = [
            [
                'roles',
                function ($query) {
                    $query->whereNot('name', 'player');
                },
            ],
        ];

        if (isset($searchParams['search_key'])) {
            $conditions[] = [
                ['id', $searchParams['search_key']],
                ['username', 'LIKE', '%' . ($searchParams['search_key']) . '%'],
                ['email', 'LIKE', '%' . ($searchParams['search_key']) . '%'],
                'OR',
            ];
        }

        if (isset($searchParams['role'])) {
            $role = Role::where('name', $searchParams['role'])->first();
            if ($role) {
                $conditions[] = [
                    'roles',
                    function ($query) use ($searchParams) {
                        $query->where('name', $searchParams['role']);
                    },
                ];
            }
        }

        return $conditions;
    }

    protected function getAdditionalOrderOptions(Request $request): array
    {
        $searchParams = $request->query();

        $orderOptions = [];

        $orderField     = $searchParams['orderField'] ?? null;
        $orderDirection = $searchParams['orderDirection'] ?? null;

        if ($orderField && in_array($orderDirection, ['desc', 'asc'])) {
            if ($orderField === 'balance') {
                $orderOptions[] = [
                    Wallet::select('balance')->whereColumn('wallets.user_id', 'users.id'),
                    $orderDirection,
                ];
            }

            if ($orderField === 'total_completed_ticket_amount') {
                $orderOptions[] = '( SELECT SUM(tickets.amount) FROM form_domains JOIN tickets ON tickets.domain_id = form_domains.id WHERE form_domains.client_id = users.id AND tickets.status = "completed" ) ' . strtoupper($orderDirection);
            }

            if ($orderField === 'avg_daily_completed_ticket_amount') {
                $orderOptions[] = '( SELECT AVG(completed_amount) FROM ( SELECT form_domains.client_id, DATE(tickets.created_at) date, SUM(amount) completed_amount FROM tickets JOIN form_domains ON tickets.domain_id = form_domains.id WHERE status = "completed" GROUP BY form_domains.client_id, date ) daily_completed_amount WHERE client_id = users.id ) ' . strtoupper($orderDirection);
            }
        }

        return $orderOptions;
    }

    protected function getAdditionalConditionsForRelatedData($request, $id, $model): array
    {
        $modelName = strtolower(class_basename($model));

        $searchParams = $request->query();

        $conditions = [];

        if ($modelName === 'ticket') {
            $user = User::with('roles')->find($id);
            if ($user->role !== 'fulfiller') {
                $conditions[] = [false];
            }
            if (isset($searchParams['searchKey'])) {
                $conditions[] = ['ticket_id', 'LIKE', '%' . $searchParams['searchKey'] . '%'];
            }

            if (isset($searchParams['status'])) {
                $conditions[] = ['status', $searchParams['status']];
            }
        }

        return $conditions;
    }

    protected function getAdditionalOrderOptionsForRelatedData($request, $id, $model): array
    {
        $modelName = strtolower(class_basename($model));

        $searchParams = $request->query();

        $orderOptions = [];

        if ($modelName === 'ticket') {
            if (($searchParams['processing_tickets_first'] ?? 'false') === 'true') {
                $orderOptions[] = "( `status` = 'processing' ) DESC";
            }
        }

        return $orderOptions;
    }

    protected function afterGet(Request $request, &$data)
    {
        $searchParams = $request->query();

        if (in_array('wallet', $searchParams['with'] ?? [])) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['stats'] = $this->repositories['user']->getStatsById($data[$i]['id']);
            }
        }

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['stats'] = $this->repositories['user']->getStatsById($data[$i]['id']);
        }
        return null;
    }

    protected function afterGetRelatedDataById_v2(Request $request, &$data, $relationName)
    {
        $currentUser = $request->user();

        if ($relationName === 'domains' && $currentUser->role === 'player') {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['commission_percentage'] =
                    ($data[$i]['commission_percentage']['admin_customer'] ?? null ?: 4) +
                    ($data[$i]['commission_percentage']['distributor_customer'] ?? null ?: 0);
            }
        }

        return null;
    }

    protected function fillData(Request $request, &$data, $op): void
    {
        $currentUser = $request->user();

        if ($op === 'c') {
            $data['status'] = 'active';

            if ($currentUser->role === 'distributor') {
                $data['distributor_id'] = $currentUser->id;
            }
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
    }

    protected function afterUpsert(Request $request, $data, $originalRecord, $updatedRecord): mixed
    {
        if (array_key_exists('role', $data)) {
            $role = Role::where('name', $data['role'])->first();
            if ($role) {
                $updatedRecord->roles()->sync([$role->id]);
            }
        }

        return null;
    }

    // protected function checkIfRowCanBeDeletedById($id): bool
    // {
    //     $user = Auth::user();

    //     $query = User::where('id', $id)
    //         ->whereNot('id', $user->id);

    //     if ($user->role === 'master_admin') {
    //         $row = $query
    //             ->where(function ($query) {
    //                 $query
    //                     ->whereHas('roles', function ($query) {
    //                         $query->whereNot('name', 'master_admin');
    //                     })
    //                     ->orWhereDoesntHave('roles');
    //             })->first();
    //         if ($row) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }
}
