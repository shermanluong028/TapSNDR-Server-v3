<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    // use SoftDeletes;

    protected $fillable = ['user_id', 'activity_type', 'description', 'ip_address', 'country', 'user_agent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'            => [
                'r' => ['master_admin'],
            ],
            'user_id'       => [
                'r' => ['master_admin'],
            ],
            'activity_type' => [
                'r' => ['master_admin'],
            ],
            'description'   => [
                'r' => ['master_admin'],
            ],
            'ip_address'    => [
                'r' => ['master_admin'],
            ],
            'country'       => [
                'r' => ['master_admin'],
            ],
            'user_agent'    => [
                'r' => ['master_admin'],
            ],
            'created_at'    => [
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
