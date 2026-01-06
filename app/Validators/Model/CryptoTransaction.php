<?php
namespace App\Validators\Model;

use App\Models\Ticket;
use App\Models\User;
use App\Rules\NotNull;

class CryptoTransaction extends Validator
{
    public function __construct()
    {
        parent::__construct(\App\Models\CryptoTransaction::class);
    }

    protected function validateEmpty($data, $allowedFields): mixed
    {
        $requiredFields = ['user_id', 'amount', 'description', 'transaction_type'];

        if (in_array($data['transaction_type'] ?? null, ['credit', 'debit'])) {
            $requiredFields[] = 'transaction_hash';
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
            'user_id'          => [new NotNull],
            'amount'           => 'numeric|min:0',
            'transaction_type' => 'in:credit,debit,deposit,withdraw',
            'transaction_hash' => [new NotNull],
        ]);
        if ($validator->fails()) {
            return $validator->errors()->first();
        }

        // if (array_key_exists('user_id', $data)) {
        $user = User::find($data['user_id']);
        if (! $user) {
            return trans('validation.invalid', ['field' => 'user_id']);
        }
        // }

        if (in_array($data['transaction_type'], ['credit', 'debit'])) {
            $ticket = Ticket::where('ticket_id', $data['transaction_hash'])->first();
            if (! $ticket) {
                return trans('validation.invalid', ['field' => 'transaction_hash']);
            }
        } else if ($data['transaction_type'] === 'deposit') {
            if (array_key_exists('transaction_hash', $data)) {
                $transaction = \App\Models\CryptoTransaction::whereRaw('LOWER(transaction_hash) = "' . strtolower($data['transaction_hash']) . '"')
                    ->where('transaction_type', 'deposit')
                    ->first();
                if ($transaction) {
                    return 'Duplicated Transaction Hash';
                }
            }
        }

        return null;
    }
}