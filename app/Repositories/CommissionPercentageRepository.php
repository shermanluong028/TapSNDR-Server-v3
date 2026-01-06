<?php
namespace App\Repositories;

use App\Contracts\Repositories\CommissionPercentageRepository as CommissionPercentageRepositoryInterface;
use App\Models\CommissionPercentage;

class CommissionPercentageRepository extends Repository implements CommissionPercentageRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(
            model: CommissionPercentage::class,
            validator: \App\Validators\Model\CommissionPercentage::class,
        );
    }

    protected function fillData(&$data, $op): void
    {
        $data['admin_client']         = $data['admin_client'] ?? 1;
        $data['admin_customer']       = $data['admin_customer'] ?? 4;
        $data['distributor_client']   = $data['distributor_client'] ?? 0;
        $data['distributor_customer'] = $data['distributor_customer'] ?? 0;
        return;
    }
}
