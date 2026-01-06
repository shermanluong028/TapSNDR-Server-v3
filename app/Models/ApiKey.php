<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiKey extends Model
{
    use SoftDeletes;

    protected $fillable = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        return [false];
    }
}
