<?php
namespace App\Http\Controllers;

use App\Models\FormPaymentMethod;

class PaymentMethodsController extends BaseController
{
    public function __construct()
    {
        parent::__construct(
            model: new FormPaymentMethod,
        );
    }
}
