<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Settings extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'         => [
                'r' => ['*'],
            ],
            'user_id'    => [
                'r' => ['*'],
                'c' => ['*'],
                'u' => ['*'],
            ],
            'created_at' => [
            ],
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        return [['user_id', $currentUser->id]];
    }
}
