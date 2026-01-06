<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class FormGameOption extends Model
{
    // use SoftDeletes;

    protected $fillable = ['game_name', 'domain_id', 'display_order', 'active'];

    public function domain()
    {
        return $this->belongsTo(FormDomain::class, 'domain_id');
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'            => [
                'r' => ['master_admin', 'distributor', 'player', 'guest'],
            ],
            'game_name'     => [
                'r' => ['master_admin', 'distributor', 'player', 'guest'],
            ],
            'domain_id'     => [
                'r' => ['master_admin', 'distributor', 'player', 'guest'],
            ],
            'display_order' => [
                'r' => ['master_admin', 'distributor'],
            ],
            'active'        => [
                'r' => ['master_admin', 'distributor'],
            ],
            'created_at'    => [
                'r' => ['master_admin', 'distributor'],
            ],
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        if (
            ! $currentUser ||
            $currentUser->role === 'master_admin' ||
            $currentUser->role === 'player'
        ) {
            return [true];
        } else if ($currentUser->role === 'distributor') {
            return [
                'domain',
                function ($query) use ($currentUser) {
                    $query->whereHas('client', function ($query) use ($currentUser) {
                        $query->where('distributor_id', $currentUser->id);
                    });
                },
            ];
        }
        return [false];
    }
}
