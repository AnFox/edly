<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Payment;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use SSitdikov\ATOL\Client\ApiClient;
use SSitdikov\ATOL\Object\Info;
use SSitdikov\ATOL\Object\Item;
use SSitdikov\ATOL\Object\Payment as AtolPayment;
use SSitdikov\ATOL\Object\Receipt;
use SSitdikov\ATOL\Object\ReceiptSno;
use SSitdikov\ATOL\Request\OperationRequest;
use SSitdikov\ATOL\Request\ReportRequest;
use SSitdikov\ATOL\Request\TokenRequest;
use SSitdikov\ATOL\Response\OperationResponse;
use SSitdikov\ATOL\Response\TokenResponse;

class ProcessPaymentCloudPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Payment
     */
    private $payment;

    /**
     * Create a new job instance.
     *
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // send request to ATOL online to get the receipt
        $order = $this->payment->order;

        $price = (float)$order->amount;
        $clientEmail = $order->user->email;

        /** @var User $user */
        $user = $order->user;

        $companyAddress = config('services.atol-online.sale_point');

        if ($user->isAn('owner')) {
            // Edly is the seller
//            Log::debug('Edly is the seller');
            $companyInn = config('services.atol-online.inn');

            $atolLogin = config('services.atol-online.login');
            $atolPassword = config('services.atol-online.password');
            $atolGroup = config('services.atol-online.group');
        } else {
            // Account is the seller
//            Log::debug('Account is the seller');
            /** @var Account $account */
            $account = $order->account;

            $paymentSettings = $account->getOption('cashbox');
            $companyInn = $paymentSettings['inn'];

            $atolLogin = $paymentSettings['login'];
            $atolPassword = $paymentSettings['password'];
            $atolGroup = $paymentSettings['group'];
        }

        $httpClient = null;

        if (App::environment() !== 'production') {
            $httpClient = new Client(
                [
                    'base_uri' => 'https://testonline.atol.ru/possystem/v4/'
                ]
            );
        }

        $client = new ApiClient($httpClient);

        try {
            /**
             * @var TokenResponse $token
             */
            $token = $client->getToken(
                new TokenRequest($atolLogin, $atolPassword)
            );

            try {
                $uuid = "{$order->id}/{$order->payment->id}/{$order->payment->updated_at}";
                $groupId = $atolGroup;
                $item = new Item($order->product ? $order->product->name : $order->description, $price, 1, Item::TAX_NONE);
                $paymentElectr = new AtolPayment(AtolPayment::PAYMENT_TYPE_ELECTR, $price);

                $receipt = new Receipt();
                $receipt->setItems([$item])
                    ->setSno(ReceiptSno::RECEIPT_SNO_USN_INCOME_OUTCOME)
                    ->setInn($companyInn)
                    ->setEmail($clientEmail)
                    ->setPaymentAddress($companyAddress)
                    ->setPayments([$paymentElectr]);

                $payment_address = $companyAddress;
                $info = new Info($companyInn, $payment_address);
//                $callback_url = 'http://test.mystore.dev/callback/api/url';
//                $info = new Info($companyInn, $payment_address, $callback_url);

                /**
                 * @var OperationResponse $operation
                 */
                $operation = $client->doOperation(
                    new OperationRequest($groupId, OperationRequest::OPERATION_SELL, $uuid, $receipt, $info, $token)
                );
                $uuidAtol = $operation->getUuid();
                sleep(10);

                try {
                    $report = $client->getReport(
                        new ReportRequest($groupId, $uuidAtol, $token)
                    );

                    Log::channel('sentry')->info('ATOL Online: чек успешно зарегистрирован по заказу #' . $order->id, [
                        'report' => $report,
                    ]);

                } catch (\Exception $e) {
                    Log::channel('sentry')->error('Ошибка получения отчета о регистрации чека', ['exception' => $e]);
                }
            } catch (\Exception $e) {
                Log::channel('sentry')->error('Ошибка регистрации чека', ['exception' => $e]);
            }

        } catch (\Exception $e) {
            Log::channel('sentry')->error('Ошибка получения токена для ATOL online', ['exception' => $e]);
        }
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($exception);
        }
    }
}
