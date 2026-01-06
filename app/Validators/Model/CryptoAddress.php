<?php
namespace App\Validators\Model;

use App\Models\User;

class CryptoAddress extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\CryptoAddress::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        $requiredFields = ['user_id', 'address'];

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
            'user_id' => 'integer',
            'address' => 'string|size:42',
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

        if (array_key_exists('address', $data)) {
            $cryptoAddress = \App\Models\CryptoAddress::where('address', $data['address'])->first();
            if ($cryptoAddress && ($op !== 'u' || $data['id'] != $cryptoAddress->id)) {
                return trans('validation.duplicated', ['field' => 'address']);
            }
        }

        return null;
    }
}
