<?php

namespace App\Http\Controllers\Webhooks;

use App\Contracts\Services\PurchaseService;
use App\Http\Controllers\Controller;
use App\Services\CloudPaymentsPurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CloudPaymentsController
 * @package App\Http\Controllers\Webhooks
 */
class CloudPaymentsController extends Controller
{
    /**
     * @var CloudPaymentsPurchaseService
     */
    private $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    private function responseSuccess(): JsonResponse
    {
        return response()->json(["code" => CloudPaymentsPurchaseService::RESPONSE_CODE_OK]);
    }

    public function check(Request $request): JsonResponse
    {
        $this->purchaseService->checkPayment($request);

        return $this->responseSuccess();
    }

    public function pay(Request $request): JsonResponse
    {
        $this->purchaseService->createPaymentFromRequest($request);

        return $this->responseSuccess();
    }

    public function fail(Request $request): JsonResponse
    {
        $this->purchaseService->failPayment($request);

        return $this->responseSuccess();
    }

    public function cancel(Request $request): JsonResponse
    {
        $this->purchaseService->cancelPayment($request);

        return $this->responseSuccess();
    }

    public function refund(Request $request): JsonResponse
    {
        $this->purchaseService->refundPayment($request);

        return $this->responseSuccess();
    }


}
