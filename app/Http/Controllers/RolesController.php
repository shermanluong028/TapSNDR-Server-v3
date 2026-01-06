<?php
namespace App\Http\Controllers;

use App\Models\Role;

class RolesController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: new Role,
        );
    }
}
