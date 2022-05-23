<?php


namespace App\Contracts\Services;


use App\Models\Order;
use App\Models\Payment;

/**
 * Interface PurchaseService
 * @package App\Contracts\Services
 */
interface PurchaseService
{
    public function createPayment(Order $order, string $paymentStatus = Payment::PAYMENT_STATUS_DRAFT , string $description = null, int $paymentMethodId = null, bool $isPaid = false);

    public function chargeByToken(float $amount, string $currencyCode, string $accountId, string $token, array $params = []);

    public function getFailReasonCodeMessage($code);
}
