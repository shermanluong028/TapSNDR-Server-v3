<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class FormPaymentMethod extends Model
{
    // use SoftDeletes;

    protected $fillable = ['method_name', 'active'];

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'            => [
                'r' => ['master_admin', 'user', 'player', 'guest'],
            ],
            'method_name'   => [
                'r' => ['master_admin', 'user', 'player', 'guest', 'api'],
            ],
            'domain_id'     => [
                'r' => ['master_admin'],
            ],
            'display_order' => [
                'r' => ['master_admin'],
            ],
            'active'        => [
                'r' => ['master_admin'],
                'c' => ['master_admin'],
                'u' => ['master_admin'],
            ],
            'created_at'    => [
                'r' => ['master_admin'],
            ],
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        $userRole = $currentUser->role ?? 'guest';

        if ($userRole === 'master_admin') {
            return [true];
        } else {
            return [['active', 1]];
        }
    }
}
