<?php
namespace App\Models;

use App\Helpers\Utils;
use Illuminate\Database\Eloquent\Model;

class TicketCompletionImage extends Model
{
    // use SoftDeletes;

    protected $fillable = [];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public static function getAllowedFields($role, $op)
    {
        $mapRolesToFields = [
            'id'         => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
            'ticket_id'  => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
            'image_path' => [
                'r' => ['master_admin', 'distributor', 'user', 'player'],
            ],
            'created_at' => [
                'r' => [],
            ],
        ];
        return Utils::getAllowedFields($mapRolesToFields, $role, $op);
    }

    public static function getConditionsForReadableRecords($currentUser): array
    {
        if ($currentUser->role === 'master_admin') {
            return [true];
        } else if ($currentUser->role === 'distributor') {
            return [
                [
                    'ticket',
                    function ($query) use ($currentUser) {
                        $query->whereHas('domain', function ($query) use ($currentUser) {
                            $query->whereHas('client', function ($query) use ($currentUser) {
                                $query->where('distributor_id', $currentUser->id);
                            });
                        });
                    },
                ],
            ];
        } else if ($currentUser->role === 'user') {
            return [
                [
                    'ticket',
                    function ($query) use ($currentUser) {
                        $query->whereHas('domain', function ($query) use ($currentUser) {
                            $query->where('client_id', $currentUser->id);
                        });
                    },
                ],
            ];
        } else if ($currentUser->role === 'player') {
            return [
                [
                    'ticket',
                    function ($query) use ($currentUser) {
                        $query->where('player_id', $currentUser->id);
                    },
                ],
            ];
        }
        return [false];
    }
}
