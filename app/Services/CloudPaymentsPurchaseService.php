<?php


namespace App\Services;


use App\Contracts\Repositories\UserRepository;
use App\Contracts\Services\PurchaseService;
use App\Jobs\ProcessPaymentCloudPayments;
use App\Jobs\ProcessPaymentRefundCloudPayments;
use App\Models\Account;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PaymentFailed;
use App\Notifications\PaymentSucceed;
use Carbon\Carbon;
use CloudPayments\Exception\PaymentException;
use CloudPayments\Exception\RequestException;
use CloudPayments\Manager;
use CloudPayments\Model\Required3DS;
use CloudPayments\Model\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class CloudPaymentsPurchaseService
 * @package App\Services
 */
class CloudPaymentsPurchaseService implements PurchaseService
{
    const RESPONSE_CODE_OK = 0;
    const RESPONSE_CODE_WRONG_ORDER_NUMBER = 10; // Payment will be rejected
    const RESPONSE_CODE_WRONG_AMOUNT = 11; // Payment will be rejected
    const RESPONSE_CODE_PAYMENT_UNACCEPTABLE = 13; // Payment will be rejected
    const RESPONSE_CODE_PAYMENT_OUTDATED = 20; // Payment will be rejected, payer will be informed

    /**
     * @var Manager
     */
    private $client;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserService
     */
    private $userService;

    /**
     * CloudPaymentsPurchaseService constructor.
     * @param UserRepository $userRepository
     * @param UserService $userService
     */
    public function __construct(UserRepository $userRepository, UserService $userService)
    {
        $publicKey = config('services.cloud-payments.public-key');
        $privateKey = config('services.cloud-payments.private-key');

        $this->client = new Manager($publicKey, $privateKey);
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    private $failCode = [
        5001 => [
            'name' => 'Refer To Card Issuer',
            'reason' => 'Отказ эмитента проводить онлайн операцию',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5003 => [
            'name' => 'Invalid Merchant',
            'reason' => 'Отказ эмитента проводить онлайн операцию',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5004 => [
            'name' => 'Pick Up Card',
            'reason' => 'Карта потеряна',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5005 => [
            'name' => 'Do Not Honor',
            'reason' => 'Отказ эмитента без объяснения причин',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5006 => [
            'name' => 'Error',
            'reason' => 'Отказ сети проводить операцию или неправильный CVV код',
            'message' => 'Проверьте правильность введенных данных карты или воспользуйтесь другой картой',
        ],
        5007 => [
            'name' => 'Pick Up Card Special Conditions',
            'reason' => 'Карта потеряна',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5012 => [
            'name' => 'Invalid Transaction',
            'reason' => 'Карта не предназначена для онлайн платежей',
            'message' => 'Воспользуйтесь другой картой или свяжитесь с банком, выпустившим карту',
        ],
        5013 => [
            'name' => 'Amount Error',
            'reason' => 'Слишком маленькая или слишком большая сумма операции',
            'message' => 'Проверьте корректность суммы',
        ],
        5014 => [
            'name' => 'Invalid Card Number',
            'reason' => 'Некорректный номер карты',
            'message' => 'Проверьте правильность введенных данных карты или воспользуйтесь другой картой',
        ],
        5015 => [
            'name' => 'No Such Issuer',
            'reason' => 'Эмитент не найден',
            'message' => 'Проверьте правильность введенных данных карты или воспользуйтесь другой картой',
        ],
        5019 => [
            'name' => 'Transaction Error',
            'reason' => 'Отказ эмитента без объяснения причин',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5030 => [
            'name' => 'Format Error',
            'reason' => 'Ошибка на стороне эквайера — неверно сформирована транзакция',
            'message' => 'Повторите попытку позже',
        ],
        5031 => [
            'name' => 'Bank Not Supported By Switch',
            'reason' => 'Неизвестный эмитент карты',
            'message' => 'Воспользуйтесь другой картой',
        ],
        5033 => [
            'name' => 'Expired Card Pickup',
            'reason' => 'Истёк срок утери карты',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5034 => [
            'name' => 'Suspected Fraud',
            'reason' => 'Отказ эмитента — подозрение на мошенничество',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5036 => [
            'name' => 'Restricted Card',
            'reason' => 'Карта не предназначена для платежей',
            'message' => 'Платежи для этой карты запрещены. Попробуйте другую карту',
        ],
        5041 => [
            'name' => 'Lost Card',
            'reason' => 'Карта потеряна',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5043 => [
            'name' => 'Stolen Card',
            'reason' => 'Карта украдена',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5051 => [
            'name' => 'Insufficient Funds',
            'reason' => 'Недостаточно средств',
            'message' => 'Недостаточно средств на карте',
        ],
        5054 => [
            'name' => 'Expired Card',
            'reason' => 'Карта просрочена или неверно указан срок действия',
            'message' => 'Проверьте правильность введенных данных карты или воспользуйтесь другой картой',
        ],
        5057 => [
            'name' => 'Transaction Not Permitted',
            'reason' => 'Ограничение на карте',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5062 => [
            'name' => 'Restricted Card 2',
            'reason' => 'Карта не предназначена для платежей',
            'message' => 'Платежи для этой карты запрещены. Попробуйте другую карту',
        ],
        5063 => [
            'name' => 'Security Violation',
            'reason' => 'Карта заблокирована из-за нарушений безопасности',
            'message' => 'Воспользуйтесь другой картой',
        ],
        5065 => [
            'name' => 'Exceed Withdrawal Frequency',
            'reason' => 'Превышен лимит операций по карте',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5082 => [
            'name' => 'Incorrect CVV',
            'reason' => 'Неверный CVV код',
            'message' => 'Неверно указан код CVV',
        ],
        5091 => [
            'name' => 'Timeout',
            'reason' => 'Эмитент недоступен',
            'message' => 'Повторите попытку позже или воспользуйтесь другой картой',
        ],
        5092 => [
            'name' => 'Cannot Reach Network',
            'reason' => 'Эмитент недоступен',
            'message' => 'Повторите попытку позже или воспользуйтесь другой картой',
        ],
        5096 => [
            'name' => 'System Error',
            'reason' => 'Ошибка банка-эквайера или сети',
            'message' => 'Повторите попытку позже',
        ],
        5204 => [
            'name' => 'Unable To Process',
            'reason' => 'Операция не может быть обработана по прочим причинам',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5206 => [
            'name' => 'Authentication failed',
            'reason' => '3-D Secure авторизация не пройдена',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5207 => [
            'name' => 'Authentication unavailable',
            'reason' => '3-D Secure авторизация недоступна',
            'message' => 'Свяжитесь с вашим банком или воспользуйтесь другой картой',
        ],
        5300 => [
            'name' => 'Anti Fraud',
            'reason' => 'Лимиты эквайера на проведение операций',
            'message' => 'Воспользуйтесь другой картой',
        ],
    ];

    /**
     * @param int $code
     * @return mixed
     */
    public function getFailReasonCodeMessage($code)
    {
        return array_key_exists($code, $this->failCode) ? $this->failCode[$code]['message'] : null;
    }

    /**
     * @return Manager
     */
    public function getClient(): Manager
    {
        return $this->client;
    }

    public function checkSignature(Request $request): void
    {
        $paymentInfo = $request->all();

        $order = Order::find($paymentInfo['InvoiceId']);
        $account = $order->account;
        if ($account) {
            $key = $account->getOption('payment.CloudPayments.private_key');
//            Log::debug('key from account', compact('key'));
        } else {
            $key = config('services.cloud-payments.private-key');
//            Log::debug('key from service', compact('key'));
        }

        $signatureFromContent = base64_encode(hash_hmac('sha256', $request->getContent(), $key, true));
        $signatureFromRequest = $request->header('Content-HMAC');

        if ($signatureFromContent !== $signatureFromRequest) {
            Log::notice('signature from content does not match signature from request', compact('signatureFromContent', 'signatureFromRequest'));
            response()->json(["code" => self::RESPONSE_CODE_PAYMENT_UNACCEPTABLE])->send();
            exit;
        }
    }

    private function checkInvoiceId(array $paymentInfo): void
    {
        if (!isset($paymentInfo['InvoiceId'])) {
            Log::channel('sentry')->error('CloudPayment callback has no required InvoiceId', [
                'paymentInfo' => $paymentInfo,
            ]);

            response()->json(["code" => self::RESPONSE_CODE_WRONG_ORDER_NUMBER])->send();
            exit;
        }
    }

    private function checkOrder(array $paymentInfo): void
    {
        $order = Order::find($paymentInfo['InvoiceId']);
        if (!$order) {
            Log::channel('sentry')->error('CloudPayment callback process error: failed to find Order matching InvoiceId', [
                'paymentInfo' => $paymentInfo,
            ]);

            response()->json(["code" => self::RESPONSE_CODE_WRONG_ORDER_NUMBER])->send();
            exit;
        }
    }

    private function checkPaymentTimeStamp(array $paymentInfo): void
    {
        $order = Order::find($paymentInfo['InvoiceId']);
        $paymentTS = Carbon::createFromTimeString($paymentInfo['DateTime']);

        if ($paymentTS->lt($order->payment->payment_ts)) {
            Log::channel('sentry')->notice('CloudPayment Payment timestamp in callback is less than previously processed for Order #' . $order->id, [
                'paymentInfo' => $paymentInfo,
            ]);

            response()->json(["code" => 0])->send();
            exit;
        }
    }

    public function validateCallback(array $paymentInfo): void
    {
        $this->checkInvoiceId($paymentInfo);

        $this->checkOrder($paymentInfo);

        $this->checkPaymentTimeStamp($paymentInfo);
    }

    public function checkPayment(Request $request): void
    {
//        Log::debug(__METHOD__ . ': checkSignature');
        try {
            $this->checkSignature($request);
        } catch(\Exception $e) {
            Log::error(__METHOD__ . ': check signature exception caught', [$e]);
        }
//        Log::debug(__METHOD__ . ': Signature checked');

        $paymentInfo = $request->all();
//        Log::debug(__METHOD__ . ': checkInvoiceId');
        $this->checkInvoiceId($paymentInfo);
//        Log::debug(__METHOD__ . ': InvoiceId checked');

//        Log::debug(__METHOD__ . ': checkOrder');
        $this->checkOrder($paymentInfo);
//        Log::debug(__METHOD__ . ': Order checked');
    }

    public function createPaymentFromRequest(Request $request)
    {
        $this->checkSignature($request);

        $paymentInfo = $request->all();
        $this->validateCallback($paymentInfo);

        /** @var Order $order */
        $order = Order::find($paymentInfo['InvoiceId']);
        $order->status_id = OrderStatus::ORDER_STATUS_PAID;
        $order->save();

        $paymentTS = Carbon::createFromTimeString($paymentInfo['DateTime']);
        $payment = $order->payment;
        $payment->payment_id = $paymentInfo['TransactionId'];
        $payment->payment_ts = $paymentTS;
        $payment->status = Payment::PAYMENT_STATUS_SUCCEEDED;
        $payment->paid = true;
        $payment->amount = $paymentInfo['Amount'];
        $payment->payment_method = '';
        $payment->description = $paymentInfo['Description'];
        $payment->test = $paymentInfo['TestMode'] ?: false;
        $payment->save();

        $payment->setOption('success', $paymentInfo);

        if ($user = $order->user) {
            // There is a user in the order
            /** @var User $user */
            if ($user->isAn('owner')) {
                // save token to the owner account and set account active
                /** @var Account $account */
                $account = $this->userRepository->setModel($user)->getFirstLinkedAccount();
                $account->payment_token = $paymentInfo['Token'];
                $account->has_card = true;
                $account->setOption('card', [
                    'type' => $paymentInfo['CardType'],
                    'lastFourDigits' => $paymentInfo['CardLastFour'],
                ]);
                $account->status = Account::STATUS_ACTIVE;

                if ($order->product && $order->product->type === Product::TYPE_BALANCE_REFILL) {
                    /** @var AccountService $accountService */
                    $accountService = app(AccountService::class);
                    $amount = $accountService->processTrialAmount($account, $order->amount);

                    $account->balance = $account->balance + $amount;
                } else {
                    // Successfully charged for users and we can restore the balance
                    $account->balance = $account->balance + $order->amount;
                    if (!empty($paymentInfo['Data'])) {
                        $webinar = $order->webinar;
                        $data = json_decode($paymentInfo['Data']);
                        if ($data->userIdList) {
                            $users = User::whereIn('id', $data->userIdList)->get();
                            $users->each(function ($user) use ($webinar) {
                                $this->userRepository->setModel($user)->setWebinarChargedSuccessfully($webinar);
                            });
                        }
                    }
                }
                $account->save();
            }
            $user->notify(new PaymentSucceed($payment->fresh()));
        } else {
            // There is no user in the order
            if (!empty($paymentInfo['Email'])) {
                try {
                    if (!$user = $this->userRepository->getByEmail($paymentInfo['Email'])) {
                        $user = $this->userService->createByEmail($paymentInfo['Email']);
                    }

                    // Set user for the order
                    $order->user_id = $user->id;
                    $order->save();

                    /** @var User $user */
                    $user->notify(new PaymentSucceed($payment->fresh()));
                } catch (\Exception $e) {
                    Log::error('An error occurred when there is no user in the order', [$e]);
                }
            }
        }

        // Register receipt
        if ($account = $order->account) {
            // Account is the seller
            /** @var Account $account */
            $cashboxSystem = $account->getOption('cashbox.system');
            if ($cashboxSystem === 'ATOL') {
                // Send payment info to ATOL Online
                ProcessPaymentCloudPayments::dispatch($payment->fresh());
            }
        } else {
            // Edly is the seller
            ProcessPaymentCloudPayments::dispatch($payment->fresh());
            $user->account->finishTrial();
        }
    }

    public function failPayment(Request $request): JsonResponse
    {
        $this->checkSignature($request);

        $paymentInfo = $request->all();
        $this->validateCallback($paymentInfo);

        /** @var Order $order */
        $order = Order::find($paymentInfo['InvoiceId']);
        $order->status_id = OrderStatus::ORDER_STATUS_PENDING;
        $order->save();

        $paymentTS = Carbon::createFromTimeString($paymentInfo['DateTime']);
        $payment = $order->payment;
        $payment->payment_id = $paymentInfo['TransactionId'];
        $payment->payment_ts = $paymentTS;
        $payment->status = Payment::PAYMENT_STATUS_FAILED;
        $payment->fail_reason_code = $paymentInfo['ReasonCode'];
        $payment->fail_reason_text = $paymentInfo['Reason'];
        $payment->paid = false;
        $payment->amount = $paymentInfo['Amount'];
        $payment->payment_method = '';
        $payment->description = $paymentInfo['Description'];
        $payment->test = $paymentInfo['TestMode'] ?: false;
        $payment->save();

        $payment->setOption('fail', $paymentInfo);

        /** @var User $user */
        if ($user = $order->user) {
            // set account inactive and unset payment token and has card flag
            $user->notify(new PaymentFailed($payment));

            if ($user->isAn('owner')) {

                $account = $this->userRepository->setModel($user)->getFirstLinkedAccount();
//            $account->payment_token = null;
                $account->has_card = false;
                $account->status = Account::STATUS_SUSPENDED;
                $account->save();
            }
        }


        return response()->json(["code" => 0]);
    }

    public function cancelPayment(Request $request): void
    {
        $this->checkSignature($request);

        $paymentInfo = $request->all();
        $this->validateCallback($paymentInfo);

        $order = Order::find($paymentInfo['InvoiceId']);
        $order->status_id = OrderStatus::ORDER_STATUS_CANCELED;
        $order->save();

        $paymentTS = Carbon::createFromTimeString($paymentInfo['DateTime']);

        $payment = $order->payment;
        $payment->payment_id = $paymentInfo['TransactionId'];
        $payment->payment_ts = $paymentTS;
        $payment->status = Payment::PAYMENT_STATUS_CANCELED;
        $payment->paid = false;
        $payment->amount = $paymentInfo['Amount'];
        $payment->payment_method = '';
        $payment->description = $paymentInfo['Description'];
        $payment->test = $paymentInfo['TestMode'] ?: false;
        $payment->save();

        // @todo: Send payment cancel command to ATOL Online
    }

    public function refundPayment(Request $request): void
    {
        $this->checkSignature($request);

        $paymentInfo = $request->all();
        $this->validateCallback($paymentInfo);

        $order = Order::find($paymentInfo['InvoiceId']);
        $order->status_id = OrderStatus::ORDER_STATUS_CANCELED;
        $order->save();

        $paymentTS = Carbon::createFromTimeString($paymentInfo['DateTime']);

        $payment = $order->payment;
        $payment->payment_id = $paymentInfo['TransactionId'];
        $payment->payment_ts = $paymentTS;
        $payment->status = Payment::PAYMENT_STATUS_REFUNDED;
        $payment->paid = false;
        $payment->amount = $paymentInfo['Amount'];
        $payment->payment_method = '';
        $payment->save();

        /** @var Account $account */
        if ($account = $order->account) {
            $cashboxSystem = $account->getOption('cashbox.system');
            if ($cashboxSystem === 'ATOL') {
                // Send payment refund command to ATOL Online
                ProcessPaymentRefundCloudPayments::dispatch($payment);
            }
        }
    }

    /**
     * @param Order $order
     * @param string $paymentStatus
     * @param string|null $description
     * @param int $paymentMethodId
     * @param bool $isPaid
     * @return Payment
     */
    public function createPayment(Order $order, string $paymentStatus = Payment::PAYMENT_STATUS_DRAFT, string $description = null, int $paymentMethodId = null, bool $isPaid = false)
    {
        $payment = new Payment();
        $payment->order_id = $order->id;
        $payment->payment_id = '';
        $payment->status = $paymentStatus;
        $payment->paid = $isPaid;
        $payment->amount = $order->amount;
        $payment->payment_method = '';
        $payment->payment_method_id = $paymentMethodId;
        $payment->description = $description;
        $payment->test = false;
        $payment->save();

        return $payment;
    }

    /**
     * @param float $amount
     * @param string $currencyCode
     * @param string $accountId
     * @param string $token
     * @param array $params
     * @return Required3DS|Transaction
     * @throws PaymentException
     * @throws RequestException
     */
    public function chargeByToken(float $amount, string $currencyCode, string $accountId, string $token, array $params = [])
    {
        return $this->client->chargeToken($amount, $currencyCode, $accountId, $token, $params);
    }
}
