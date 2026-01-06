<?php
namespace App\Validators\Model;

use App\Models\Role;
use App\Rules\NotNull;

class User extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\User::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        $requiredFields = ['username', 'email', 'role', 'password'];

        foreach ($requiredFields as $field) {
            if (
                in_array($field, $allowedFields) &&
                ! array_key_exists($field, $data)
            ) {
                return trans('validation.required', ['field' => $field]);
            }
        }

        // if (
        //     in_array('role', $allowedFields) &&
        //     ! array_key_exists($field, $data) &&
        //     $data['role'] === 'user'
        // ) {
        //     if (
        //         in_array('domains', $allowedFields) &&
        //         ! array_key_exists('domains', $data)
        //     ) {
        //         return trans('validation.required', ['field' => 'domains']);
        //     }
        // }

        return null;
    }

    protected function validateFields($data, $allowedFields, $op): mixed
    {
        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'username' => [new NotNull],
            'email'    => 'nullable|email',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        if (array_key_exists('username', $data)) {
            $user = \App\Models\User::where('username', $data['username'])->first();
            if ($user && ($op !== 'u' || $data['id'] != $user->id)) {
                return trans('validation.duplicated', ['field' => 'username']);
            }
        }

        if (isset($data['email'])) {
            $user = \App\Models\User::where('email', $data['email'])->first();
            if ($user && ($op !== 'u' || $data['id'] != $user->id)) {
                return trans('validation.duplicated', ['field' => 'email']);
            }
        }

        if (array_key_exists('password', $data)) {
            if ($op === 'c') {
                if (is_null($data['password'])) {
                    return trans('validation.required', ['field' => 'password']);
                }
            }
        }

        if (isset($data['role'])) {
            $role = Role::where('name', $data['role'])->first();
            if (! $role) {
                return trans('validation.invalid', ['field' => 'role']);
            }
        }

        return null;
    }
}
