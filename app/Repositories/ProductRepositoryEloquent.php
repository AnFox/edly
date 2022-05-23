<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Http\Requests\Admin\ProductDuplicateRequest;
use App\Models\Product;
use Illuminate\Support\Arr;

/**
 * Class ProductRepositoryEloquent
 * @package App\Repositories
 */
class ProductRepositoryEloquent extends BaseRepositoryEloquent implements ProductRepository
{
    protected $model;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * ProductRepositoryEloquent constructor.
     * @param Product $model
     * @param WebinarRepository $webinarRepository
     */
    public function __construct(Product $model, WebinarRepository $webinarRepository)
    {
        $this->setModel($model);
        $this->webinarRepository = $webinarRepository;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->getModel()->price;
    }

    /**
     * @return string
     */
    public function getPriceWithCurrencySign()
    {
        return $this->getPrice() . ' ' . $this->getModel()->currency->sign;
    }

    /**
     * @return mixed
     */
    public function getAccountRefillProduct()
    {
        return $this->model->whereType(Product::TYPE_BALANCE_REFILL)->firstOrFail();
    }

    public function getWebinar()
    {
        $roomId = $this->model->banner->room_id;

        return $this->webinarRepository->getCurrentWebinar($roomId);
    }

    public function getListByAccountId(int $id)
    {
        $this->setModel($this->model->whereAccountId($id));

        return $this->paginate();
    }

    /**
     * @param $source
     * @param ProductDuplicateRequest $request
     * @return Product
     */
    public function duplicate($source, ProductDuplicateRequest $request)
    {
        /** @var Product $source */
        $attributesSrc = $source->toArray();
        $attributesDst = $request->validated();

        $attributes = array_merge(Arr::except($attributesSrc, ['id']), $attributesDst);

        /** @var Product $destination */
        $destination = $this->create($attributes);

        // @TODO: Duplicate background images
        // $this->duplicateMediaCollection($source, $destination, Room::MEDIA_COLLECTION_THUMBNAIL);

        return $destination;
    }
}
