<?php
namespace App\Validators\Model;

use App\Models\User;
use App\Rules\NotNull;

class FormDomain extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\FormDomain::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        $requiredFields = ['vendor_code', 'group_name', 'telegram_chat_id'];

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
            'vendor_code'           => [new NotNull],
            'group_name'            => [new NotNull],
            'telegram_chat_id'      => [new NotNull],
            'active'                => 'in:1,0',
            'original_form_enabled' => 'in:1,0',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        if (isset($data['client_id'])) {
            $user = User::where('id', $data['client_id'])
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'user');
                })
                ->first();
            if (! $user) {
                return trans('validation.invalid', ['field' => 'client_id']);
            }
        }

        if (array_key_exists('vendor_code', $data)) {
            $domain = \App\Models\FormDomain::where('vendor_code', $data['vendor_code'])->first();
            if ($domain && ($op !== 'u' || $data['id'] != $domain->id)) {
                return trans('validation.duplicated', ['field' => 'vendor_code']);
            }
        }

        if (array_key_exists('telegram_chat_id', $data)) {
            $domain = \App\Models\FormDomain::where('telegram_chat_id', $data['telegram_chat_id'])->first();
            if ($domain && ($op !== 'u' || $data['id'] != $domain->id)) {
                return trans('validation.duplicated', ['field' => 'telegram_chat_id']);
            }
        }

        return null;
    }
}
