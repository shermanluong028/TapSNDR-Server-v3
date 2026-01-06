<?php
namespace App\Http\Controllers;

use App\Models\PaymentDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentDetailsController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: new PaymentDetails,
            validator: new \App\Validators\Model\PaymentDetails,
            options: [
                'files' => [
                    'qrcode' => [
                        'path'      => 'public/qrcodes',
                        'extension' => ['png', 'jpg'],
                    ],
                ],
            ]
        );
    }

    protected function fillData(Request $request, &$data, $operation): void
    {
        if ($operation === 'c') {
            $currentUser = $request->user();
            if ($currentUser?->roles[0]?->name === 'player') {
                $data['user_id'] = $currentUser->id;
            }
        }

        if (array_key_exists('qrcode_url', $data)) {
            if ($data['qrcode_url'] === 'null') {
                $data['qrcode_url'] = null;
            }
        }

        if (array_key_exists('qrcode', $data)) {
            $data['qrcode_url'] = url(Storage::url($data['qrcode']['storage_path']));
        }
    }

    protected function afterUpsert(Request $request, $data, $originalRecord, $updatedRecord): mixed
    {
        // Cash App
        if (str_contains($updatedRecord->method->method_name, 'Cash App')) {
            $updatedRecord->update([
                'email'        => $updatedRecord->phone_number ? null : $updatedRecord->email,
                'phone_number' => $updatedRecord->email ? null : $updatedRecord->phone_number,
            ]);
        }

        // Zelle
        if (str_contains($updatedRecord->method->method_name, 'Zelle')) {
            $updatedRecord->update([
                'tag'          => null,
                'email'        => $updatedRecord->phone_number ? null : $updatedRecord->email,
                'phone_number' => $updatedRecord->email ? null : $updatedRecord->phone_number,
            ]);
        }

        // Chime
        if (str_contains($updatedRecord->method->method_name, 'Chime')) {
            $updatedRecord->update([
                'email'        => $updatedRecord->phone_number ? null : $updatedRecord->email,
                'phone_number' => $updatedRecord->email ? null : $updatedRecord->phone_number,
            ]);
        }

        // PayPal
        if (str_contains($updatedRecord->method->method_name, 'PayPal')) {
            $updatedRecord->update([
                'email'        => $updatedRecord->phone_number ? null : $updatedRecord->email,
                'phone_number' => $updatedRecord->email ? null : $updatedRecord->phone_number,
            ]);
        }

        // Apple Pay
        if (str_contains($updatedRecord->method->method_name, 'Apple Pay')) {
            $updatedRecord->update([
                'tag'          => null,
                'email'        => null,
                'account_name' => null,
                'qrcode_url'   => null,
            ]);
        }

        // Venmo
        if (str_contains($updatedRecord->method->method_name, 'Venmo')) {
            $updatedRecord->update([
                'email'        => $updatedRecord->phone_number ? null : $updatedRecord->email,
                'phone_number' => $updatedRecord->email ? null : $updatedRecord->phone_number,
            ]);
        }

        // Skrill
        if (str_contains($updatedRecord->method->method_name, 'Skrill')) {
            $updatedRecord->update([
                'tag'          => null,
                'email'        => $updatedRecord->phone_number ? null : $updatedRecord->email,
                'phone_number' => $updatedRecord->email ? null : $updatedRecord->phone_number,
                'account_name' => null,
            ]);
        }

        return null;
    }
}
