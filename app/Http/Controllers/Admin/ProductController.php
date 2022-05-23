<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductDuplicateRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\ProductToggleVisibilityRequest;
use App\Http\Resources\ProductResource;
use App\Models\Currency;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Class ProductController
 * @package App\Http\Controllers
 */
class ProductController extends Controller
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ProductController constructor.
     * @param ProductRepository $productRepository
     * @param UserRepository $userRepository
     */
    public function __construct(ProductRepository $productRepository, UserRepository $userRepository)
    {
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('view-products', $this->productRepository->getClass());

        $user = request()->user();
        $account = $this->userRepository->setModel($user)->getFirstLinkedAccount();

        $products = $this->productRepository->getListByAccountId($account->id);

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductRequest $request
     * @return ProductResource
     * @throws AuthorizationException
     */
    public function store(ProductRequest $request): ProductResource
    {
        $this->authorize('create-product', $this->productRepository->getClass());

        $user = $request->user();
        $account = $this->userRepository->setModel($user)->getFirstLinkedAccount();

        $attributes = $request->validated();
        $attributes['account_id'] = $account->id;
        $attributes['currency_id'] = Currency::RUB;
        $product = $this->productRepository->create($attributes);

        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return ProductResource
     * @throws AuthorizationException
     */
    public function show($id): ProductResource
    {
        $product = $this->productRepository->findOrFail($id)->getModel();
        $this->authorize('view-product', $product);

        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProductRequest $request
     * @param int $id
     * @return ProductResource
     * @throws AuthorizationException
     */
    public function update(ProductRequest $request, $id): ProductResource
    {
        $product = $this->productRepository->findOrFail($id)->getModel();
        $this->authorize('update-product', $product);

        $this->productRepository->fill($request->validated());
        $this->productRepository->save();
        $product = $this->productRepository->getModel();

        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $product = $this->productRepository->findOrFail($id)->getModel();
        $this->authorize('delete-product', $product);

        $this->productRepository->delete();

        return response()->json(['result' => 'success']);
    }

    /**
     * Duplicate the specified resource in storage.
     *
     * @param ProductDuplicateRequest $request
     * @param int $id
     * @return ProductResource
     * @throws AuthorizationException
     */
    public function duplicate(ProductDuplicateRequest $request, int $id): ProductResource
    {
        $this->authorize('create-product', $this->productRepository->getClass());
        $source = $this->productRepository->find($id)->getModel();

        $product = $this->productRepository->duplicate($source, $request);

        return new ProductResource($product);
    }
}
