<?php


namespace App\Services;


use App\Contracts\Services\PurchaseService;
use App\Jobs\ProcessPaymentYandexCheckout;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use YandexCheckout\Client;

class YandexPurchaseService implements PurchaseService
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client->setAuth(env('YANDEX_SHOP_ID'), env('YANDEX_SHOP_SECRET'));
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    public function createPayment(Order $order): RedirectResponse
    {
        $description = 'Покупка на сайте ' . env('APP_URL');

        $paymentYandexCheckout = $this->getClient()->createPayment([
            'amount' => [
                'value' => $order->amount,
                'currency' => $order->currency->code,
            ],
            'metadata' => [
                'user_id' => $order->user->id,
                'order_id' => $order->id,
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => env('YANDEX_REDIRECT_URL') . '/' . $order->id,
            ],
            'capture' => true,
            'description' => $description,
            'receipt' => [
                'email' => $order->user->email,
                'items' => $this->getItems($order),
            ],
        ],
            uniqid('', true));

        $payment = new Payment();
        $payment->order_id = $order->id;
        $payment->payment_id = $paymentYandexCheckout->id;
        $payment->status = $paymentYandexCheckout->status;
        $payment->paid = $paymentYandexCheckout->paid;
        $payment->amount = '';
        $payment->payment_method = '';
        $payment->description = $paymentYandexCheckout->description;
        $payment->test = $paymentYandexCheckout->test ?: false;
        $payment->save();

        ProcessPaymentYandexCheckout::dispatch($payment);

        return redirect($paymentYandexCheckout->confirmation->confirmationUrl);
    }

    protected function getItems(Order $order): array
    {
        $items = [];

        foreach ($order->items as $orderItem) {
            array_push($items, [
                'description' => 'delivery',
                'quantity' => $orderItem->quantity,
                'amount' => [
                    'value' => (string)$orderItem->price,
                    'currency' => $orderItem->currency->code,
                ],
                'vat_code' => '1', // без ндс
                'payment_mode' => 'full_payment',
                'payment_subject' => 'service',
            ]);
        }

        return $items;
    }
}
