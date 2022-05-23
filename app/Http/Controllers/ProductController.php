<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\OrderRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Contracts\Services\PurchaseService;
use App\Http\Requests\CreateProductOrderRequest;
use App\Http\Requests\ProductGetPublicInfoRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\PublicProductOrderResource;

class ProductController extends Controller
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var PurchaseService
     */
    private $purchaseService;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * ProductController constructor.
     * @param ProductRepository $productRepository
     * @param OrderRepository $orderRepository
     * @param WebinarRepository $webinarRepository
     * @param PurchaseService $purchaseService
     */
    public function __construct(
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
        WebinarRepository $webinarRepository,
        PurchaseService $purchaseService
    )
    {
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->purchaseService = $purchaseService;
        $this->webinarRepository = $webinarRepository;
    }

    public function getPublicInfo(ProductGetPublicInfoRequest $request)
    {
        $product = $this->productRepository->find($request->get('productId'))->getModel();

        return new ProductResource($product);
    }

    public function createOrder(CreateProductOrderRequest $request)
    {
        $webinar = $this->productRepository->find($request->get('product_id'))->getWebinar();
        $this->webinarRepository->setModel($webinar);
        $account = $this->webinarRepository->getAccount();

        $attributes = $request->validated();
        $attributes['webinar_id'] = $webinar->id;
        $attributes['account_id'] = $account->id;
        $order = $this->orderRepository->create($attributes);

        $this->purchaseService->createPayment($order);

        return new PublicProductOrderResource($order);
    }
}
