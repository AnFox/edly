<?php


namespace App\Filters;


use App\Contracts\Repositories\OrderRepository;
use App\Repositories\OrderRepositoryEloquent;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FilterOrdersBySearch implements Filter
{

    public function __invoke(Builder $query, $value, string $property)
    {
        /** @var OrderRepositoryEloquent $orderRepository */
        $orderRepository = app(OrderRepository::class);

        if (intval($value)) {
            $orderRepository->setBuilder($query)->filterById(intval($value));
        } else {
            $orderRepository->setBuilder($query)->filterByStatus($value);
        }
    }
}
