<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    // use SoftDeletes;

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    protected $fillable = ['player_id', 'domain_id', 'facebook_name', 'ticket_id', 'user_id', 'payment_method', 'payment_tag', 'account_name', 'amount', 'game', 'game_id', 'image_path', 'status', 'chat_group_id'];

    public function domain()
    {
        return $this->belongsTo(FormDomain::class, 'domain_id');
    }

    public function player()
    {
        return $this
            ->belongsTo(User::class, 'player_id')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'player');
            });
    }

    public function client()
    {
        return $this
            ->hasOneThrough(
                User::class,
                FormDomain::class,
                'id',
                'id',
                'domain_id',
                'client_id'
            )
            ->whereHas('roles', function ($query) {
                $query->where('name', 'user');
            });
    }

    public function validation_image()
    {
        return $this->hasOne(CompletionImage::class, 'form_id');
    }

    public function fulfiller()
    {
        return $this
            ->belongsTo(User::class, 'user_id')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'fulfiller');
            });
    }

    public function completion_images()
    {
        return $this->hasMany(TicketCompletionImage::class);
    }

    public function transactions()
    {
        return $this
            ->hasMany(CryptoTransaction::class, 'transaction_hash', 'ticket_id')
            ->whereIn('transaction_type', ['credit', 'debit']);
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'                  => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
            'player_id'           => [
                'r' => ['master_admin', 'user', 'player'],
                'c' => ['player'],
            ],
            'domain_id'           => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
                'c' => ['api', 'player'],
            ],
            'facebook_name'       => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
                'c' => ['api', 'player'],
            ],
            'ticket_id'           => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
            'user_id'             => [
                'r' => ['master_admin', 'distributor'],
                'u' => ['master_admin'],
            ],
            'payment_method'      => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
                'c' => ['api', 'player'],
            ],
            'payment_tag'         => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
                'c' => ['api', 'player'],
            ],
            'account_name'        => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
                'c' => ['api', 'player'],
            ],
            // 'payment_details_id'  => [
            //     'c' => ['player'],
            // ],
            'amount'              => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
                'c' => ['api', 'player'],
            ],
            'game'                => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
                'c' => ['api', 'player'],
            ],
            'game_id'             => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
                'c' => ['api', 'player'],
            ],
            'image_path'          => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
            'qrcode'              => [ // for image_path
                'c' => ['api'],
            ],
            'status'              => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
            'chat_group_id'       => [
                'r' => ['master_admin', 'distributor'],
                'c' => ['player'],
            ],
            'completion_time'     => [
                'r' => ['master_admin', 'distributor'],
            ],
            'completed_at'        => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
            'completed_by'        => [
                'r' => ['master_admin'],
            ],
            'error_type'          => [
                'r' => ['master_admin', 'distributor'],
            ],
            'error_details'       => [
                'r' => ['master_admin', 'distributor'],
            ],
            'error_reported_at'   => [
                'r' => ['master_admin', 'distributor'],
            ],
            'error_reported_by'   => [
                'r' => ['master_admin'],
            ],
            'telegram_message_id' => [
                'r' => ['master_admin', 'distributor'],
            ],
            'telegram_chat_id'    => [
                'r' => ['master_admin', 'distributor'],
            ],
            'created_at'          => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        if ($currentUser->role === 'master_admin') {
            return [true];
        } else if ($currentUser->role === 'distributor') {
            return [
                [
                    'domain',
                    function ($query) use ($currentUser) {
                        $query->whereHas('client', function ($query) use ($currentUser) {
                            $query->where('distributor_id', $currentUser->id);
                        });
                    },
                ],
            ];
        } else if ($currentUser->role === 'user') {
            return [
                [
                    'domain',
                    function ($query) use ($currentUser) {
                        $query->where('client_id', $currentUser->id);
                    },
                ],
            ];
        } else if ($currentUser->role === 'player') {
            return [['player_id', $currentUser->id]];
        }
        return [false];
    }
}
