<?php
namespace App\Validators\Model;

use App\Models\FormDomain;
use App\Models\User;
use App\Rules\NotNull;

class Ticket extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\Ticket::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        $requiredFields = ['domain_id', 'facebook_name', 'amount', 'game', 'game_id'];

        if (! array_key_exists('player_id', $data)) {
            array_push($requiredFields, 'payment_method', 'payment_tag', 'account_name', 'qrcode');
        }

        foreach ($requiredFields as $field) {
            if (
                in_array($field, $allowedFields) &&
                ! array_key_exists($field, $data)
            ) {
                return trans('validation.required', ['field' => $field]);
            }
        }

        return null;
    }

    protected function validateFields($data, $allowedFields, $op): mixed
    {
        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'player_id'      => [new NotNull],
            'domain_id'      => [new NotNull],
            'facebook_name'  => [new NotNull],
            'user_id'        => [new NotNull],
            'payment_method' => [new NotNull],
            'payment_tag'    => [new NotNull],
            'account_name'   => [new NotNull],
            'amount'         => 'numeric|min:100',
            'game'           => [new NotNull],
            'game_id'        => [new NotNull],
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        if (array_key_exists('player_id', $data)) {
            $player = User::where('id', $data['player_id'])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'player');
                })
                ->first();
            if (! $player) {
                return trans('validation.invalid', ['field' => 'player_id']);
            }
        }

        if (array_key_exists('domain_id', $data)) {
            $domain = FormDomain::find($data['domain_id']);
            if (! $domain) {
                return trans('validation.invalid', ['field' => 'domain_id']);
            }
        }

        if (array_key_exists('user_id', $data)) {
            $fulfiller = User::where('id', $data['user_id'])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'fulfiller');
                })
                ->first();
            if (! $fulfiller) {
                return trans('validation.invalid', ['field' => 'user_id']);
            }
        }

        return null;
    }
}
