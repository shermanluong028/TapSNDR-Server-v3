<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompletionImage extends Model
{
    use SoftDeletes;

    protected $fillable = [];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'form_id');
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
