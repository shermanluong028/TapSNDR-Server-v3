<?php
namespace App\Validators\Model;

use App\Models\User;
use App\Rules\NotNull;

class Settings extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\FormDomain::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        $requiredFields = ['user_id'];

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
            'user_id'               => [new NotNull],
            'low_balance_threshold' => 'nullable|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        if (array_key_exists('user_id', $data)) {
            $user = User::find($data['user_id']);
            if (! $user) {
                return trans('validation.invalid', ['field' => 'user_id']);
            }
        }

        return null;
    }
}
