<?php
namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;

class SettingsController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: new Settings,
            validator: new \App\Validators\Model\Settings
        );
    }

    protected function fillData(Request $request, &$data, $operation): void
    {
        $currentUser = $request->user();

        if ($operation === 'c') {
            if (! array_key_exists('user_id', $data)) {
                $data['user_id'] = $currentUser->id;
            }
        }
    }
}
