<?php

namespace App\Repositories;

use App\Contracts\Repositories\OrderRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Filters\FilterOrdersBySearch;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Class CurrencyRepositoryEloquent
 * @package App\Repositories
 */
class OrderRepositoryEloquent extends BaseRepositoryEloquent implements OrderRepository
{
    protected $model;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * CurrencyRepositoryEloquent constructor.
     * @param Order $model
     * @param ProductRepository $productRepository
     * @param WebinarRepository $webinarRepository
     */
    public function __construct(Order $model, ProductRepository $productRepository, WebinarRepository $webinarRepository)
    {
        $this->setModel($model);
        $this->model = $model;
        $this->productRepository = $productRepository;
        $this->webinarRepository = $webinarRepository;
    }

    /**
     * @param array $attributes
     * @return $this|mixed
     */
    public function create(array $attributes)
    {
        if (empty($attributes['amount']) && empty($attributes['currency_code']) && !empty($attributes['product_id'])) {
            $product = $this->productRepository->find($attributes['product_id'])->getModel();
            $attributes['amount'] = $product->price;
            $attributes['currency_code'] = $product->currency->code;
        }

        return parent::create($attributes);
    }

    public function userOrders()
    {
        $builder = QueryBuilder::for($this->getModel()->where('user_id', Auth::user()->id))
            ->allowedFilters([
                AllowedFilter::scope('startDate'),
                AllowedFilter::scope('endDate'),
                AllowedFilter::custom('search', new FilterOrdersBySearch)
            ]);
        $this->setBuilder($builder);

        return $this;
    }

    public function filterByDateRange($dateFrom, $dateTo)
    {
        $this->getBuilder()->whereDate('created_at', '>=', $dateFrom);
        $this->getBuilder()->whereDate('created_at', '<=', $dateTo);

        return $this;
    }

    public function filterById(int $id)
    {
        $this->getBuilder()->where('id', $id);

        return $this;
    }

    public function filterByStatus(string $status)
    {
        $status = trim($status);
        $statusNameAlias = null;

        switch (mb_strtolower($status)) {
            case 'в ожидании':
                $statusName = 'pending';
                $statusNameAlias = 'draft';
                break;
            case 'выполнен':
                $statusName = 'paid';
                break;
            case 'отменен':
                $statusName = 'canceled';
                break;
            case 'не выполнен':
                $statusName = 'failed';
                break;
            default:
                $statusName = null;
                break;
        }
        $query = OrderStatus::whereName($statusName);
        if ($statusNameAlias) {
            $query->orWhere('name', $statusNameAlias);
        }
        $orderStatus = $query->first();

        $statusId = $orderStatus ? $orderStatus->id : -1;
        $this->getBuilder()->where('status_id', $statusId);

        return $this;
    }
}
