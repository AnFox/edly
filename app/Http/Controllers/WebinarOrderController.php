<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\OrderRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Contracts\Services\PurchaseService;
use App\Http\Requests\CreateWebinarOrderRequest;
use App\Http\Resources\WebinarOrderResource;
use Illuminate\Http\Response;

/**
 * Class WebinarOrderController
 * @package App\Http\Controllers
 */
class WebinarOrderController extends Controller
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
     * @var WebinarRepository
     */
    private $webinarRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * WebinarOrderController constructor.
     * @param OrderRepository $orderRepository
     * @param WebinarRepository $webinarRepository
     * @param PurchaseService $purchaseService
     * @param ProductRepository $productRepository
     */
    public function __construct(
        OrderRepository $orderRepository,
        WebinarRepository $webinarRepository,
        PurchaseService $purchaseService,
        ProductRepository $productRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->purchaseService = $purchaseService;
        $this->webinarRepository = $webinarRepository;
        $this->productRepository = $productRepository;
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
     * @param CreateWebinarOrderRequest $request
     * @return WebinarOrderResource
     */
    public function store(CreateWebinarOrderRequest $request): WebinarOrderResource
    {
        $attributes = $request->validated();
        $attributes['user_id'] = $request->user()->id;

        $webinarRepositoryInstance = $this->webinarRepository->find($attributes['webinar_id']);
        $account = $webinarRepositoryInstance->getAccount();
        $attributes['account_id'] = $account->id;

        $product = $this->productRepository->find($attributes['product_id'])->getModel();
        $webinar = $webinarRepositoryInstance->getModel();
        $attributes['description'] = 'Покупка товара ' . $product->name . ' в вебинаре ' . $webinar->name;

        $order = $this->orderRepository->create($attributes);
        $this->purchaseService->createPayment($order);

        return new WebinarOrderResource($order);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CreateWebinarOrderRequest $request
     * @param int $id
     * @return Response
     */
    public function update(CreateWebinarOrderRequest $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
