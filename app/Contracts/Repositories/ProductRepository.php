<?php


namespace App\Contracts\Repositories;


use App\Http\Requests\Admin\ProductDuplicateRequest;

/**
 * Interface ProductRepository
 * @package App\Contracts\Repositories
 */
interface ProductRepository extends BaseRepository {
    /**
     * @return float
     */
    public function getPrice();

    public function getWebinar();

    /**
     * @return string
     */
    public function getPriceWithCurrencySign();

    /**
     * @return mixed
     */
    public function getAccountRefillProduct();

    public function getListByAccountId(int $id);

    public function duplicate($source, ProductDuplicateRequest $request);
}
