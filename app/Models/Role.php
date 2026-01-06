<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // use SoftDeletes;

    protected $fillable = ['name', 'description'];

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'          => [
                'r' => ['master_admin', 'distributor'],
            ],
            'name'        => [
                'r' => ['master_admin', 'distributor'],
            ],
            'description' => [
                'r' => ['master_admin'],
            ],
            'created_at'  => [
                'r' => ['master_admin'],
            ],
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        if ($currentUser->role === 'master_admin') {
            return [true];
        } else if ($currentUser->role === 'distributor') {
            return [['name', 'user']];
        }
        return [false];
    }
}
