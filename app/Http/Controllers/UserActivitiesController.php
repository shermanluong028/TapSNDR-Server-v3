<?php
namespace App\Http\Controllers;

use App\Models\UserActivityLog;

class UserActivitiesController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: new UserActivityLog,
        );
    }
}
