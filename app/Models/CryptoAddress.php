<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CryptoAddress extends Model
{
    use SoftDeletes;

    protected $table = 'user_crypto_addresses';

    protected $fillable = ['user_id', 'address'];

    public function user()
    {
        return $this->belongsTo(User::class);
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
            'address'    => [
                'r' => ['master_admin'],
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
        }
        return [false];
    }
}
