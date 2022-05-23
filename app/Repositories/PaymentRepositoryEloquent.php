<?php

namespace App\Repositories;

use App\Contracts\Repositories\PaymentRepository;
use App\Models\Payment;

/**
 * Class PaymentRepositoryEloquent
 * @package App\Repositories
 */
class PaymentRepositoryEloquent extends BaseRepositoryEloquent implements PaymentRepository
{
    protected $model;

    /**
     * PaymentRepositoryEloquent constructor.
     * @param Payment $model
     */
    public function __construct(Payment $model)
    {
        $this->setModel($model);
    }
}
