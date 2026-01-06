<?php
namespace App\Http\Controllers;

use App\Contracts\Repositories\TicketRepository;
use App\Helpers\Utils;
use App\Models\CryptoTransaction;
use App\Models\FormDomain;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TicketsController extends BaseController
{
    public function __construct(
        TicketRepository $ticketRepository
    ) {
        parent::__construct(
            model: new Ticket,
            validator: new \App\Validators\Model\Ticket,
            options: [
                'files' => [
                    'qrcode' => [
                        'path'      => 'public/qrcodes',
                        'extension' => ['png', 'jpg'],
                    ],
                ],
            ]
        );
        $this->repositories['ticket'] = $ticketRepository;
    }

    public function getStats()
    {
        $stats = $this->repositories['ticket']->getStats();

        return Utils::responseData($stats);
    }

    public function getDailyTotalAmount(Request $request)
    {
        $searchParams = $request->query();

        $validator = Validator::make($searchParams, [
            'timezone' => 'required',
        ]);
        if ($validator->fails()) {
            return Utils::responseError($validator->errors()->first());
        }

        $data = $this->repositories['ticket']->getDailyTotalAmount($searchParams);

        return Utils::responseData($data);
    }

    public function getValidationImage(Request $request, $id)
    {
        $currentUser = $request->user();

        $ticket = Ticket::find($id);
        if (! $ticket || ! $ticket->validation_image) {
            abort(404);
        }

        if ($currentUser->role === 'user') {
            if ($ticket->domain->client_id !== $currentUser->id) {
                abort(403);
            }
        }

        $response = Http::get($ticket->validation_image->image_path);
        if ($response->failed()) {
            abort(404);
        }

        return response($response->body(), 200)->header('Content-Type', $response->header('Content-Type'));
    }

    public function refund(Request $request, $id)
    {
        $payload = $request->post();

        $validator = Validator::make($payload, [
            'amount'            => 'required|numeric|min:0',
            'inconvenience_fee' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        $ticket = Ticket::find($id);
        if (! $ticket) {
            return Utils::responseError('Invalid Ticket ID');
        }

        if ($ticket->status !== 'completed') {
            return Utils::responseError('Refunds can only be processed for completed tickets.');
        }

        $commissionPercentage = $ticket->domain->commission_percentage?->toArray();
        $commissionPercentage = array_merge(
            config('commission.default_percentage'),
            (array) $commissionPercentage
        );

        DB::beginTransaction();

        // Admin
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        $refundAmount =
            $payload['amount'] / (1 - ($commissionPercentage['admin_customer'] + $commissionPercentage['distributor_customer']) / 100) *
            (($commissionPercentage['admin_client'] + $commissionPercentage['admin_customer']) / 100 - (1 - $commissionPercentage['admin_customer'] / 100) * 0.03) +
            $payload['inconvenience_fee'];

        $admin->wallet->decrement('balance', $refundAmount);

        CryptoTransaction::create([
            'user_id'          => $admin->id,
            'amount'           => $refundAmount,
            'description'      => 'Refund due to incomplete ticket processing (including inconvenience fee)',
            'transaction_hash' => $ticket->ticket_id,
            'transaction_type' => 'debit',
        ]);

        // Fulfiller
        $refundAmount = $payload['amount'] * 1.03;

        $ticket->fulfiller->wallet->decrement('balance', $refundAmount);

        CryptoTransaction::create([
            'user_id'          => $ticket->fulfiller->id,
            'amount'           => $refundAmount,
            'description'      => 'Refund due to incomplete ticket processing',
            'transaction_hash' => $ticket->ticket_id,
            'transaction_type' => 'debit',
        ]);

        // Client
        $refundAmount =
            $payload['amount'] / (1 - ($commissionPercentage['admin_customer'] + $commissionPercentage['distributor_customer']) / 100) *
            (1 + $commissionPercentage['admin_client'] / 100) +
            $payload['inconvenience_fee'];

        $ticket->client->wallet->increment('balance', $refundAmount);

        CryptoTransaction::create([
            'user_id'          => $ticket->client->id,
            'amount'           => $refundAmount,
            'description'      => 'Refund due to incomplete ticket processing (including inconvenience fee)',
            'transaction_hash' => $ticket->ticket_id,
            'transaction_type' => 'credit',
        ]);

        DB::commit();

        return Utils::responseData();
    }

    protected function checkUpdatableById(?User $user, $id): bool
    {
        if (! $this->checkReadableById($user, $id)) {
            return false;
        }
        $ticket = Ticket::find($id);
        if ($ticket->status !== 'processing') {
            return false;
        }
        return true;
    }

    protected function getAdditionalConditions(Request $request): array
    {
        $currentUser  = $request->user();
        $searchParams = $request->query();

        $conditions = [];

        if (isset($searchParams['searchKey'])) {
            $conditions[] = [
                ['ticket_id', 'LIKE', '%' . $searchParams['searchKey'] . '%'],
                // [
                //     'domain',
                //     function ($query) use ($searchParams) {
                //         $query
                //             ->where('domain', 'LIKE', '%' . $searchParams['searchKey'] . '%')
                //             ->orWhereHas('client', function ($query) use ($searchParams) {
                //                 $query->where('username', 'LIKE', '%' . $searchParams['searchKey'] . '%');
                //             });
                //     },
                // ],
                // [
                //     'player',
                //     function ($query) use ($searchParams) {
                //         $query
                //             ->where('username', 'LIKE', '%' . $searchParams['searchKey'] . '%')
                //             ->orWhereHas('payment_details', function ($query) use ($searchParams) {
                //                 $query
                //                     ->where('tag', 'LIKE', '%' . $searchParams['searchKey'] . '%')
                //                     ->orWhere('email', 'LIKE', '%' . $searchParams['searchKey'] . '%')
                //                     ->orWhere('phone_number', 'LIKE', '%' . $searchParams['searchKey'] . '%');

                //             });
                //     },
                // ],
                ['facebook_name', 'LIKE', '%' . $searchParams['searchKey'] . '%'],
                null,
                'OR',
            ];
        }

        if (isset($searchParams['status'])) {
            if ($currentUser->role === 'user') {
                if ($searchParams['status'] === 'sent') {
                    $searchParams['status'] = 'pending';
                }
                if ($searchParams['status'] === 'pending') {
                    $conditions[] = [
                        ['status', 'pending'],
                        ['status', 'sent'],
                        null,
                        'OR',
                    ];
                } else {
                    $conditions[] = ['status', $searchParams['status']];
                }
            } else if ($currentUser->role === 'player') {
                if (
                    $searchParams['status'] === 'sent' ||
                    $searchParams['status'] === 'validated' ||
                    $searchParams['status'] === 'processing'
                ) {
                    $searchParams['status'] = 'pending';
                }
                if (
                    $searchParams['status'] === 'reported'
                ) {
                    $searchParams['status'] = 'error';
                }
                if ($searchParams['status'] === 'pending') {
                    $conditions[] = [
                        ['status', 'pending'],
                        ['status', 'sent'],
                        ['status', 'validated'],
                        ['status', 'processing'],
                        'OR',
                    ];
                } else if ($searchParams['status'] === 'error') {
                    $conditions[] = [
                        ['status', 'error'],
                        ['status', 'reported'],
                        null,
                        'OR',
                    ];
                } else {
                    $conditions[] = ['status', $searchParams['status']];
                }
            } else {
                $conditions[] = ['status', $searchParams['status']];
            }
        }

        if (isset($searchParams['user_id'])) {
            $user = User::find($searchParams['user_id']);
            if ($user) {
                if ($user->role === 'user') {
                    $conditions[] = [
                        'domain_id',
                        function ($query) use ($user) {
                            $query->whereHas('client', function ($query) use ($user) {
                                $query->where('id', $user->id);
                            });
                        },
                    ];
                }
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

    protected function getAdditionalOrderOptions(Request $request): array
    {
        $searchParams = $request->query();

        $orderOptions = [];

        if (($searchParams['processing_tickets_first'] ?? 'false') === 'true') {
            $orderOptions[] = "( `status` = 'processing' ) DESC";
        }

        return $orderOptions;
    }

    protected function afterGet(Request $request, &$data)
    {
        $currentUser = $request->user();

        if ($currentUser->role === 'user') {
            for ($i = 0; $i < count($data); $i++) {
                if (
                    $data[$i]['status'] === 'sent'
                ) {
                    $data[$i]['status'] = 'pending';
                }
            }
        }

        if ($currentUser->role === 'player') {
            for ($i = 0; $i < count($data); $i++) {
                if (
                    $data[$i]['status'] === 'sent' ||
                    $data[$i]['status'] === 'validated' ||
                    $data[$i]['status'] === 'processing'
                ) {
                    $data[$i]['status'] = 'pending';
                }
                if (
                    $data[$i]['status'] === 'reported'
                ) {
                    $data[$i]['status'] = 'error';
                }
            }
        }

        return null;
    }

    protected function fillData(Request $request, &$data, $operation): void
    {
        $currentUser = $request->user();

        if ($operation === 'c') {
            $domain                = FormDomain::find($data['domain_id']);
            $data['ticket_id']     = strtoupper(substr($domain->domain, 0, 2)) . '-' . time() . '-' . Str::upper(Str::random(4));
            $data['status']        = 'pending';
            $data['chat_group_id'] = $domain->telegram_chat_id;
            if ($currentUser?->role === 'player') {
                $data['player_id'] = $currentUser->id;
            }
            if (in_array($domain->client->id, explode(',', env('TEST_USER_IDS')))) {
                $data['amount'] = 5;
            }
        }

        if (array_key_exists('qrcode', $data)) {
            $data['image_path'] = url(Storage::url($data['qrcode']['storage_path']));
        }
    }

    protected function afterUpsert(Request $request, $data, $originalRecord, $updatedRecord): mixed
    {
        if (! $originalRecord) {
            if (! $updatedRecord->domain->active) {
                return 'Ticket submission to this vendor has been blocked.';
            }
            if (
                (
                    ! $updatedRecord->payment_method ||
                    ! $updatedRecord->payment_tag ||
                    ! $updatedRecord->account_name ||
                    ! $updatedRecord->image_path
                ) &&
                (
                    ! $updatedRecord->coinflow_user_id ||
                    ! $updatedRecord->coinflow_account
                )
            ) {
                if ($updatedRecord->player) {
                    $paymentDetails = $updatedRecord
                        ->player
                        ->payment_details()
                        ->whereHas('method', function ($query) {
                            $query->where('active', 1);
                        })
                        ->get();
                } else {
                    $paymentDetails = [];
                }
                if (count($paymentDetails ?? []) === 0) {
                    return 'No payment methods';
                } else {
                    // $applepayEnabled = FormPaymentMethod::where('method_name', 'LIKE', '%Apple Pay%')
                    //     ->where('active', 1)
                    //     ->exists();
                    // $chimeEnabled = FormPaymentMethod::where('method_name', 'LIKE', '%Chime%')
                    //     ->where('active', 1)
                    //     ->exists();

                    // if ($applepayEnabled || $chimeEnabled) {
                    //     $submittable = $paymentDetails->contains(function ($item) use ($applepayEnabled, $chimeEnabled) {
                    //         $methodName = $item->method?->method_name ?? '';
                    //         return
                    //             (! $applepayEnabled || ! str_contains($methodName, 'Apple Pay')) &&
                    //             (! $chimeEnabled || ! str_contains($methodName, 'Chime'));
                    //     });
                    //     if (! $submittable) {
                    //         $error = 'Please add a payment method other than';
                    //         if ($applepayEnabled && $chimeEnabled) {
                    //             $error .= ' Apple Pay or Chime.';
                    //         } else if ($applepayEnabled) {
                    //             $error .= ' Apple Pay.';
                    //         } else if ($chimeEnabled) {
                    //             $error .= ' Chime.';
                    //         }
                    //         return $error;
                    //     }
                    // }
                }
            }
            if ($updatedRecord->player) {
                $updatedRecord->player->domains()->attach($updatedRecord->domain->id);
            }
        }
        if ($originalRecord?->status === 'processing' && $originalRecord->user_id !== $updatedRecord->user_id) {
            $transaction = CryptoTransaction::where('user_id', $originalRecord->user_id)
                ->where('transaction_hash', $originalRecord->ticket_id)
                ->where('transaction_type', 'credit')
                ->first();
            $creditAmount = $transaction?->amount ?: 0;
            $originalRecord->fulfiller->wallet->decrement('balance', $creditAmount);
            CryptoTransaction::create([
                'user_id'          => $originalRecord->user_id,
                'amount'           => $creditAmount,
                'description'      => 'Ticket processing cancelled',
                'transaction_hash' => $originalRecord->ticket_id,
                'transaction_type' => 'debit',
            ]);
            $updatedRecord->fulfiller->wallet->increment('balance', $creditAmount);
            CryptoTransaction::create([
                'user_id'          => $updatedRecord->user_id,
                'amount'           => $creditAmount,
                'description'      => 'Ticket processing assigned',
                'transaction_hash' => $originalRecord->ticket_id,
                'transaction_type' => 'credit',
            ]);
        }
        return null;
    }
}
