<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentDetails extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'method_id', 'tag', 'email', 'phone_number', 'account_name', 'qrcode_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function method()
    {
        return $this->belongsTo(FormPaymentMethod::class, 'method_id');
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'           => [
                'r' => ['master_admin', 'user', 'player'],
            ],
            'user_id'      => [
                'r' => ['master_admin', 'user', 'player'],
            ],
            'method_id'    => [
                'r' => ['master_admin', 'user', 'player'],
                'c' => ['player'],
                'u' => ['player'],
            ],
            'tag'          => [
                'r' => ['master_admin', 'user', 'player'],
                'c' => ['player'],
                'u' => ['player'],
            ],
            'email'        => [
                'r' => ['master_admin', 'user', 'player'],
                'c' => ['player'],
                'u' => ['player'],
            ],
            'phone_number' => [
                'r' => ['master_admin', 'user', 'player'],
                'c' => ['player'],
                'u' => ['player'],
            ],
            'account_name' => [
                'r' => ['master_admin', 'user', 'player'],
                'c' => ['player'],
                'u' => ['player'],
            ],
            'qrcode_url'   => [
                'r' => ['master_admin', 'player'],
                'u' => ['player'],
            ],
            'qrcode'       => [ // for qrcode_url
                'c' => ['player'],
                'u' => ['player'],
            ],
            'created_at'   => [
                'r' => ['master_admin', 'player'],
            ],
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        if ($currentUser->role === 'master_admin') {
            return [true];
        } else if ($currentUser->role === 'user') {
            return [
                [
                    'user',
                    function ($query) use ($currentUser) {
                        $query
                            ->whereHas('roles', function ($query) {
                                $query->where('name', 'player');
                            })
                            ->whereHas('player_tickets', function ($query) use ($currentUser) {
                                $query->whereHas('domain', function ($query) use ($currentUser) {
                                    $query->where('client_id', $currentUser->id);
                                });
                            });
                    },
                ],
                [
                    'method',
                    function ($query) {
                        $query->where('active', 1);
                    },
                ],
            ];
        } else if ($currentUser->role === 'player') {
            return [
                ['user_id', $currentUser->id],
                [
                    'method',
                    function ($query) {
                        $query->where('active', 1);
                    },
                ],
            ];
        }
        return [false];
    }
}
