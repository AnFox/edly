<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\CurrencyRepository;
use App\Contracts\Repositories\OrderRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Services\PurchaseService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountRefillOrderRequest;
use App\Http\Resources\Admin\AccountRefillOrderResource;
use Illuminate\Http\Response;

/**
 * Class AccountRefillOrderController
 * @package App\Http\Controllers
 */
class AccountRefillOrderController extends Controller
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
     * @var CurrencyRepository
     */
    private $currencyRepository;

    /**
     * AccountRefillOrderController constructor.
     * @param OrderRepository $orderRepository
     * @param PurchaseService $purchaseService
     * @param UserRepository $userRepository
     * @param ProductRepository $productRepository
     * @param CurrencyRepository $currencyRepository
     */
    public function __construct(OrderRepository $orderRepository,
                                PurchaseService $purchaseService,
                                UserRepository $userRepository,
                                ProductRepository $productRepository,
                                CurrencyRepository $currencyRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->purchaseService = $purchaseService;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AccountRefillOrderRequest $request
     * @return AccountRefillOrderResource|null
     */
    public function store(AccountRefillOrderRequest $request): ?AccountRefillOrderResource
    {
        $amount = $request->get('amount');
        $userId = $request->user()->id;

        $product = $this->productRepository->getAccountRefillProduct();
        $currencyCode = $this->currencyRepository->getDefaultCurrencyCode();

        $attributes = [];
        $attributes['user_id'] = $userId;
        $attributes['product_id'] = $product->id;
        $attributes['amount'] = $amount;
        $attributes['currency_code'] = $currencyCode;
        $attributes['description'] = $product->name;

        $order = $this->orderRepository->create($attributes);
        $this->purchaseService->createPayment($order);

        $account = $this->userRepository->find($userId)->getLinkedAccounts()->first();

        if ($account->has_card) {
            $this->purchaseService->chargeByToken(
                $order->amount,
                $order->currency_code,
                $order->user->email,
                $account->payment_token,
                [
                    'InvoiceId' => $order->id,
                ]
            );

            return null;
        }

        return new AccountRefillOrderResource($order);
    }
}
