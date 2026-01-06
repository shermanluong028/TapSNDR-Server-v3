<?php
namespace App\Http\Controllers;

use App\Models\Settings;

class SettingsController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: new Settings,
            validator: new \App\Validators\Model\Settings
        );
    }
}
