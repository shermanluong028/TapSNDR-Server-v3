<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    // use SoftDeletes;

    protected $fillable = ['address', 'private_key', 'balance'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'                => [
                'r' => ['*'],
            ],
            'user_id'           => [
                'r' => ['*'],
            ],
            'type'              => [
                'r' => ['master_admin'],
            ],
            'private_key'       => [
                'r' => ['master_admin'],
                'u' => ['master_admin'],
            ],
            'address'           => [
                'r' => ['master_admin'],
                'u' => ['master_admin'],
            ],
            'balance'           => [
                'r' => ['*'],
            ],
            'status'            => [
                'r' => ['master_admin'],
            ],
            'last_connected_at' => [
                'r' => ['master_admin'],
            ],
            'created_at'        => [
                'r' => ['master_admin'],
            ],
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        if ($currentUser->roles[0]->name === 'master_admin') {
            return [['status', '!=', 'inactive']];
        } else {
            return [
                ['status', '!=', 'inactive'],
                ['user_id', $currentUser->id],
            ];
        }
    }
}
