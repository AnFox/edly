<?php

namespace App\Repositories;

use App\Contracts\Repositories\AccountRepository;
use App\Models\Account;

/**
 * Class CurrencyRepositoryEloquent
 * @package App\Repositories
 */
class AccountRepositoryEloquent extends BaseRepositoryEloquent implements AccountRepository
{
    /** @var Account */
    protected $model;

    /**
     * CurrencyRepositoryEloquent constructor.
     * @param Account $model
     */
    public function __construct(Account $model)
    {
        $this->setModel($model);
    }

    /**
     * @return bool
     */
    public function canCreateRoom(): bool
    {
        return !$this->model->is_limited;
    }

    /**
     * @return bool
     */
    public function canCreateWebinar(): bool
    {
        return !$this->model->is_limited;
    }

    public function deleteCard(): void
    {
        $this->model->unsetOption('card');
        $this->model->payment_token = null;
        $this->model->has_card = false;
        $this->model->status = Account::STATUS_SUSPENDED;
        $this->model->save();
    }
}
