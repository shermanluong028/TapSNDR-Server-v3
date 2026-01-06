<?php
namespace App\Validators\Model;

class Withdrawal extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\Withdrawal::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        $requiredFields = ['amount', 'to'];

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
            'amount' => 'numeric|gt:0',
            'to'     => 'string|size:42',
            'status' => 'in:APPROVED,REPORT',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        return null;
    }
}
