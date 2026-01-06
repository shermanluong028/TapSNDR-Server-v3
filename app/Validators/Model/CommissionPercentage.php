<?php
namespace App\Validators\Model;

use App\Models\FormDomain;

class CommissionPercentage extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\CommissionPercentage::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        $requiredFields = ['domain_id'];

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
            'admin_client'         => 'numeric|min:0|max:10',
            'admin_customer'       => 'numeric|min:0|max:10',
            'distributor_client'   => 'numeric|min:0|max:10',
            'distributor_customer' => 'numeric|min:0|max:10',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        if (array_key_exists('domain_id', $data)) {
            $domain = FormDomain::find($data['domain_id']);
            if (! $domain) {
                return trans('validation.invalid', ['field' => 'domain_id']);
            }
            $commissionPercentage = \App\Models\CommissionPercentage::where('domain_id', $data['domain_id'])->first();
            if ($commissionPercentage && ($op !== 'u' || $data['id'] != $commissionPercentage->id)) {
                return trans('validation.duplicated', ['field' => 'domain_id']);
            }
        }

        return null;
    }
}
