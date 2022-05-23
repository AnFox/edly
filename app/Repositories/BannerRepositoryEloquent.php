<?php

namespace App\Repositories;

use App\Contracts\Repositories\BannerRepository;
use App\Contracts\Repositories\CurrencyRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Models\Banner;
use App\Models\Product;

/**
 * Class BannerRepositoryEloquent
 * @package App\Repositories
 */
class BannerRepositoryEloquent extends BaseRepositoryEloquent implements BannerRepository
{
    protected $model;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CurrencyRepository
     */
    private $currencyRepository;

    /**
     * BannerRepositoryEloquent constructor.
     * @param Banner $model
     * @param ProductRepository $productRepository
     * @param CurrencyRepository $currencyRepository
     */
    public function __construct(Banner $model,
                                ProductRepository $productRepository,
                                CurrencyRepository $currencyRepository)
    {
        $this->setModel($model);
        $this->productRepository = $productRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        $banner = $this->model->create($attributes);

        if ($attributes['is_product']) {
            $product = $this->productRepository->find($attributes['product_id'])->getModel();
            $banner->product()->associate($product);
            $banner->save();
        }

        return $banner;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->getModel()->product;
    }

    /**
     * @return mixed
     */
    public function getRoom()
    {
        return $this->model->room;
    }

    /**
     * @param int $roomId
     * @return mixed
     */
    public function getListByRoomId(int $roomId)
    {
        return $this->model->where('room_id', $roomId)->get();
    }
}
