<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use TaylorNetwork\UsernameGenerator\FindSimilarUsernames;
use TaylorNetwork\UsernameGenerator\GeneratesUsernames;

class User extends Authenticatable
{
    use SoftDeletes;
    use FindSimilarUsernames, GeneratesUsernames;
    use Notifiable;

    protected $fillable = ['username', 'email', 'password', 'phone', 'distributor_id', 'taparcadia_id'];

    protected $appends = ['role'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function tickets()
    {
        if ($this->role === 'fulfiller') {
            return $this->hasMany(Ticket::class, 'user_id');
        } else if ($this->role === 'user') {
            return $this->hasManyThrough(Ticket::class, FormDomain::class, 'client_id', 'domain_id');
        } else {
            return $this
                ->hasManyThrough(Ticket::class, FormDomain::class, 'client_id', 'domain_id')
                ->whereRaw('false');
        }
    }

    public function player_tickets()
    {
        return $this->hasMany(Ticket::class, 'player_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function distributor()
    {
        if ($this->role === 'user') {
            return $this
                ->belongsTo(User::class, 'distributor_id')
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'distributor');
                });
        } else {
            return $this
                ->belongsTo(User::class, 'distributor_id')
                ->whereRaw('false');
        }
    }

    public function domains()
    {
        if ($this->role === 'user') {
            return $this->hasMany(FormDomain::class, 'client_id');
        } else if ($this->role === 'player') {
            return $this->belongsToMany(FormDomain::class, 'user_domains', 'user_id', 'domain_id');
        } else {
            return $this
                ->belongsToMany(FormDomain::class, 'user_domains', 'user_id', 'domain_id')
                ->whereRaw('false');
        }
    }

    public function clients()
    {
        // $role = $this->role ?? 'guest';
        // if ($role === 'distributor') {
        return $this
            ->hasMany(User::class, 'distributor_id')
            ->whereHas('distributor')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'user');
            });
        // } else {
        //     return $this
        //         ->hasMany(User::class, 'distributor_id')
        //         ->whereRaw('false');
        // }
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function settings()
    {
        return $this->hasOne(Settings::class);
    }

    public function payment_details()
    {
        return $this->hasMany(PaymentDetails::class);
    }

    public function transactions()
    {
        return $this->hasMany(CryptoTransaction::class);
    }

    public function crypto_addresses()
    {
        return $this->hasMany(CryptoAddress::class);
    }

    public function activity_logs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    public function getNameAttribute()
    {
        return $this->email;
    }

    public function getRoleAttribute()
    {
        return $this->roles[0]->name ?? 'guest';
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'                    => [
                'r' => ['*'],
            ],
            'username'              => [
                'r' => ['*'],
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'email'                 => [
                'r' => ['*'],
                'c' => ['master_admin', 'distributor', 'player'],
                'u' => ['master_admin', 'distributor'],
            ],
            'password'              => [
                'c' => ['master_admin', 'distributor', 'player'],
                'u' => ['master_admin', 'distributor'],
            ],
            'phone'                 => [
                'r' => ['*'],
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'role'                  => [
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'status'                => [
                'r' => ['master_admin', 'distributor'],
            ],
            'last_login'            => [
                'r' => ['master_admin'],
            ],
            'failed_login_attempts' => [
                'r' => ['master_admin'],
            ],
            'last_login_attempt'    => [
                'r' => ['master_admin'],
            ],
            'password_changed_at'   => [
                'r' => ['master_admin'],
            ],
            'created_at'            => [
                'r' => ['master_admin', 'distributor'],
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
                    ['id', $currentUser->id],
                    [
                        [
                            'roles',
                            function ($query) {
                                $query->where('name', 'user');
                            },
                        ],
                        ['distributor_id', $currentUser->id],
                        null,
                        null,
                    ],
                    null,
                    'OR',
                ],
            ];
        } else if ($currentUser->role === 'user') {
            return [
                [
                    ['id', $currentUser->id],
                    [
                        [
                            'roles',
                            function ($query) {
                                $query->where('name', 'player');
                            },
                        ],
                        [
                            'player_tickets',
                            function ($query) use ($currentUser) {
                                $query->whereHas('domain', function ($query) use ($currentUser) {
                                    $query->where('client_id', $currentUser->id);
                                });
                            },
                        ],
                        null,
                        null,
                    ],
                    null,
                    'OR',
                ],
            ];
        } else {
            return [['id', $currentUser->id]];
        }
    }
}
