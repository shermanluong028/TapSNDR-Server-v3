<?php
namespace App\Validators\Model;

use App\Rules\NotNull;

class Wallet extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\Wallet::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        return null;
    }

    protected function validateFields($data, $allowedFields, $op): mixed
    {
        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'private_key' => [new NotNull],
            'address'     => [new NotNull],
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        return null;
    }
}
