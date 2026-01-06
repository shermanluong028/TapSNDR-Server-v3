<?php
namespace App\Validators\Model;

use App\Models\FormPaymentMethod;
use App\Models\User;
use App\Rules\NotNull;

class PaymentDetails extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\PaymentDetails::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        // TODO: "user_id" field is not required for players so the parameter for current user should be passed in this function.
        // Currently, payment detail submission is done only by players so "user_id" field has not been set as required field.
        $requiredFields = [
            // 'user_id',
            'method_id',
        ];

        if (
            in_array('method_id', $allowedFields) &&
            array_key_exists('method_id', $data)
        ) {
            $paymentMethod = FormPaymentMethod::find($data['method_id']);
            if ($paymentMethod) {
                // Cash App
                if (str_contains($paymentMethod?->method_name, 'Cash App')) {
                    array_push($requiredFields, 'tag', 'account_name', 'qrcode');
                }

                // Zelle
                if (str_contains($paymentMethod?->method_name, 'Zelle')) {
                    if (
                        ! array_key_exists('email', $data) &&
                        ! array_key_exists('phone_number', $data)
                    ) {
                        array_push($requiredFields, 'email', 'phone_number');
                    }
                    array_push($requiredFields, 'account_name');
                }

                // Chime
                if (str_contains($paymentMethod?->method_name, 'Chime')) {
                    if (
                        ! array_key_exists('email', $data) &&
                        ! array_key_exists('phone_number', $data)
                    ) {
                        array_push($requiredFields, 'email', 'phone_number');
                    }
                    array_push($requiredFields, 'tag', 'account_name', 'qrcode');
                }

                // PayPal
                if (str_contains($paymentMethod?->method_name, 'PayPal')) {
                    if (
                        ! array_key_exists('email', $data) &&
                        ! array_key_exists('phone_number', $data)
                    ) {
                        array_push($requiredFields, 'email', 'phone_number');
                    }
                    array_push($requiredFields, 'tag', 'account_name', 'qrcode');
                }

                // Apple Pay
                if (str_contains($paymentMethod?->method_name, 'Apple Pay')) {
                    array_push($requiredFields, 'phone_number');
                }

                // Venmo
                if (str_contains($paymentMethod?->method_name, 'Venmo')) {
                    if (
                        ! array_key_exists('email', $data) &&
                        ! array_key_exists('phone_number', $data)
                    ) {
                        array_push($requiredFields, 'email', 'phone_number');
                    }
                    array_push($requiredFields, 'tag', 'account_name', 'qrcode');
                }

                // Skrill
                if (str_contains($paymentMethod?->method_name, 'Skrill')) {
                    if (
                        ! array_key_exists('email', $data) &&
                        ! array_key_exists('phone_number', $data)
                    ) {
                        array_push($requiredFields, 'email', 'phone_number');
                    }
                    array_push($requiredFields, 'qrcode');
                }
            }
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
        // TODO: At least one of "email" and "phone_number" should not be null for some payment methods.
        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'user_id'      => [new NotNull],
            'method_id'    => [new NotNull],
            'tag'          => [new NotNull],
            'email'        => 'nullable|email',
            // 'phone_number' => [new NotNull],
            'account_name' => [new NotNull],
            'qrcode_url'   => 'in:null',
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

        if (array_key_exists('method_id', $data)) {
            $paymentMethod = FormPaymentMethod::find($data['method_id']);
            if (! $paymentMethod) {
                return trans('validation.invalid', ['field' => 'method_id']);
            }
        }

        return null;
    }
}
