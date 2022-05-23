<?php

namespace App\Repositories;

use App\Contracts\Repositories\CurrencyRepository;
use App\Models\Currency;

/**
 * Class CurrencyRepositoryEloquent
 * @package App\Repositories
 */
class CurrencyRepositoryEloquent extends BaseRepositoryEloquent implements CurrencyRepository
{
    protected $model;

    /**
     * CurrencyRepositoryEloquent constructor.
     * @param Currency $model
     */
    public function __construct(Currency $model)
    {
        $this->setModel($model);
    }

    public function getDefaultCurrency()
    {
        return $this->model->first();
    }


    /**
     * @return string
     */
    public function getDefaultCurrencyCode(): string
    {
        return $this->getDefaultCurrency()->code;
    }
}
