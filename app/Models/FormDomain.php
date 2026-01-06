<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class FormDomain extends Model
{
    // use SoftDeletes;

    protected $fillable = ['domain', 'group_name', 'vendor_code', 'telegram_chat_id', 'client_id', 'active', 'original_form_enabled'];

    public function client()
    {
        return $this
            ->belongsTo(User::class, 'client_id')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'user');
            });
    }

    public function commission_percentage()
    {
        return $this->hasOne(CommissionPercentage::class, 'domain_id');
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'user_domains', 'domain_id', 'user_id');
    }

    public function games()
    {
        return $this->hasMany(FormGameOption::class, 'domain_id');
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'                    => [
                'r' => ['master_admin', 'distributor', 'user', 'player', 'guest'],
            ],
            'domain'                => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
            'vendor_code'           => [
                'r' => ['master_admin', 'distributor', 'user', 'player', 'guest'],
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'image_url'             => [
                'r' => ['player', 'guest'],
            ],
            'group_name'            => [
                'r' => ['master_admin', 'distributor', 'user', 'player', 'guest'],
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'games'                 => [
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'telegram_chat_id'      => [
                'r' => ['master_admin', 'distributor'],
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'client_id'             => [
                'r' => ['master_admin', 'distributor'],
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'commission_percentage' => [
                'c' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'active'                => [
                'r' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'original_form_enabled' => [
                'r' => ['master_admin', 'distributor'],
                'u' => ['master_admin', 'distributor'],
            ],
            'created_at'            => [
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
                [
                    'client',
                    function ($query) use ($currentUser) {
                        $query->where('distributor_id', $currentUser->id);
                    },
                ],
            ];
        } else if ($currentUser->role === 'user') {
            return [['client_id', $currentUser->id]];
        } else if ($currentUser->role === 'player') {
            return [
                // [
                //     'players',
                //     function ($query) use ($currentUser) {
                //         $query->where('users.id', $currentUser->id);
                //     },
                // ],
                // TODO: Or domains with tickets submitted by current user
            ];
        }
        return [false];
    }
}
