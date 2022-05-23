<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\OrderRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Services\PurchaseService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrdersSearchRequest;
use App\Http\Resources\Admin\OrderResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class AccountBillingController
 * @package App\Http\Controllers
 */
class AccountBillingController extends Controller
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var PurchaseService
     */
    private $purchaseService;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * AccountBillingController constructor.
     * @param OrderRepository $orderRepository
     * @param PurchaseService $purchaseService
     * @param UserRepository $userRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(OrderRepository $orderRepository,
                                PurchaseService $purchaseService,
                                UserRepository $userRepository,
                                ProductRepository $productRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->purchaseService = $purchaseService;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index()
    {
        $orders = $this->orderRepository
            ->userOrders()
            ->paginate(100, 'id', 'desc');

        return OrderResource::collection($orders);
    }

    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function search()
    {
        $this->orderRepository->userOrders();

        $orders = $this->orderRepository->paginate();

        return OrderResource::collection($orders);
    }
}
