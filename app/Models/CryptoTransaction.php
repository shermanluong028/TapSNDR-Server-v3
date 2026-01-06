<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class CryptoTransaction extends Model
{
    // use SoftDeletes;

    protected $fillable = ['user_id', 'amount', 'description', 'reference_id', 'wallet_id', 'status', 'user_id_from', 'user_id_to', 'address_from', 'transaction_hash', 'address_to', 'token_type', 'transaction_type', 'is_manual'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'transaction_hash', 'ticket_id');
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'               => [
                'r' => ['master_admin', 'user'],
            ],
            'user_id'          => [
                'r' => ['master_admin', 'user'],
                'c' => ['master_admin'],
            ],
            'amount'           => [
                'r' => ['master_admin', 'user'],
                'c' => ['master_admin'],
            ],
            'description'      => [
                'r' => ['master_admin', 'user'],
                'c' => ['master_admin'],
            ],
            'reference_id'     => [
                'r' => ['master_admin'],
            ],
            'wallet_id'        => [
                'r' => ['master_admin'],
            ],
            'status'           => [
                'r' => ['master_admin'],
            ],
            'user_id_from'     => [
                'r' => ['master_admin'],
            ],
            'user_id_to'       => [
                'r' => ['master_admin'],
            ],
            'address_from'     => [
                'r' => ['master_admin', 'user'],
            ],
            'transaction_hash' => [
                'r' => ['master_admin', 'user'],
                'c' => ['master_admin'],
            ],
            'address_to'       => [
                'r' => ['master_admin'],
            ],
            'token_type'       => [
                'r' => ['master_admin'],
            ],
            'transaction_type' => [
                'r' => ['master_admin', 'user'],
                'c' => ['master_admin'],
            ],
            'is_manual'        => [
                'r' => ['master_admin'],
            ],
            'created_at'       => [
                'r' => ['master_admin', 'user'],
            ],

        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        if ($currentUser->role === 'master_admin') {
            return [true];
        } else if ($currentUser->role === 'user') {
            return [['user_id', $currentUser->id]];
        }
        return [false];
    }
}
