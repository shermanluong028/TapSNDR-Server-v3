<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommissionPercentage extends Model
{
    use SoftDeletes;

    protected $fillable = ['domain_id', 'admin_client', 'admin_customer', 'distributor_client', 'distributor_customer'];

    public function domain()
    {
        return $this->belongsTo(FormDomain::class, 'domain_id');
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'                   => [
                'r' => ['master_admin', 'distributor', 'player', 'guest'],
            ],
            'domain_id'            => [
                'r' => ['master_admin', 'distributor', 'player', 'guest'],
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'admin_client'         => [
                'r' => ['master_admin', 'distributor'],
                'c' => ['master_admin'],
                'u' => ['master_admin'],
            ],
            'admin_customer'       => [
                'r' => ['master_admin', 'distributor', 'player', 'guest'],
                'c' => ['master_admin'],
                'u' => ['master_admin'],
            ],
            'distributor_client'   => [
                'r' => ['master_admin', 'distributor'],
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'distributor_customer' => [
                'r' => ['master_admin', 'distributor', 'player', 'guest'],
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'created_at'           => [
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
                [
                    'domain',
                    function ($query) use ($currentUser) {
                        $query->whereHas('client', function ($query) use ($currentUser) {
                            $query->where('distributor_id', $currentUser->id);
                        });
                    },
                ],
            ];
        }
        return [false];
    }
}
