<?php


namespace App\Contracts\Repositories;


/**
 * Interface CurrencyRepository
 * @package App\Contracts\Repositories
 */
interface CurrencyRepository extends BaseRepository {
    public function getDefaultCurrency();

    /**
     * @return string
     */
    public function getDefaultCurrencyCode(): string;
};