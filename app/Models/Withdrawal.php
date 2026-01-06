<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    // use SoftDeletes;

    protected $table = 'withdraw';

    protected $fillable = ['user_id', 'amount', 'to', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction()
    {
        return $this
            ->hasOne(CryptoTransaction::class, 'reference_id')
            ->where('transaction_type', 'withdraw');
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'         => [
                'r' => ['master_admin'],
            ],
            'user_id'    => [
                'r' => ['master_admin'],
            ],
            'amount'     => [
                'r' => ['master_admin'],
                'c' => ['*'],
            ],
            'to'         => [
                'r' => ['master_admin'],
                'c' => ['*'],
            ],
            'status'     => [
                'r' => ['master_admin'],
                'u' => ['master_admin'],
            ],
            'created_at' => [
                'r' => ['master_admin'],
            ],
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        if ($currentUser->role === 'master_admin') {
            return [true];
        } else {
            return [['user_id', $currentUser->id]];
        }
    }
}
