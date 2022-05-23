<?php


namespace App\Contracts\Repositories;


/**
 * Interface OrderRepository
 * @package App\Contracts\Repositories
 */
interface OrderRepository extends BaseRepository {
    public function userOrders();

    public function filterByDateRange($dateFrom, $dateTo);

    public function filterById(int $id);

    public function filterByStatus(string $status);
};
