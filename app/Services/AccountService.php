<?php


namespace App\Services;


use App\Contracts\Repositories\OrderRepository;
use App\Contracts\Repositories\ProductRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Services\PurchaseService;
use App\Models\Account;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Models\Webinar;
use App\Notifications\AccountUpdated;
use App\Notifications\PaymentSucceed;
use App\Notifications\UsersLimitReached;
use function morphos\Russian\pluralize;

/**
 * Class AccountService
 * @package App\Services
 */
class AccountService
{
    /**
     * @var PurchaseService
     */
    private $purchaseService;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * AccountService constructor.
     * @param PurchaseService $purchaseService
     * @param ProductRepository $productRepository
     * @param OrderRepository $orderRepository
     * @param UserRepository $userRepository
     */
    public function __construct(PurchaseService $purchaseService,
                                ProductRepository $productRepository,
                                OrderRepository $orderRepository,
                                UserRepository $userRepository)
    {
        $this->purchaseService = $purchaseService;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Webinar $webinar
     * @param Account $account
     * @param float $amount
     * @param string $currencyCode
     * @param array $userIdList
     * @return void
     * @throws \Exception
     */
    public function chargeAccount(Webinar $webinar, Account $account, float $amount, string $currencyCode, array $userIdList): void
    {
        $description = 'Списание за посетителей вебинара №' . $webinar->id . ': ' . $webinar->getName() . ' в количестве ' . pluralize(count($userIdList), 'человек');

        $balance = $account->balance;
        $account->balance = $balance - $amount;
        $account->save();

        $orderAttributes = [];
        $orderAttributes['webinar_id'] = $webinar->id;
        $orderAttributes['user_id'] = $account->users()->first()->id;
        $orderAttributes['currency_code'] = $currencyCode;
        $orderAttributes['amount'] = $account->balance < 0 ? abs($account->balance) : $amount;
        $orderAttributes['description'] = $description;

        if ($account->balance < 0) {
            // Charge via card
            /** @var Order $order */
            $order = $this->orderRepository->create($orderAttributes);
            $paymentStatus = Payment::PAYMENT_STATUS_DRAFT;
            $paymentMethodId = Payment::PAYMENT_METHOD_CARD;
            $this->purchaseService->createPayment($order, $paymentStatus, $description, $paymentMethodId);

            $this->purchaseService->chargeByToken(
                $order->amount,
                $order->currency_code,
                $order->user->email,
                $account->payment_token,
                [
                    'InvoiceId' => $order->id,
                    'JsonData' => json_encode(compact('userIdList')),
                ]
            );
        } else {
            // Charge via debit
            $orderAttributes['status_id'] = OrderStatus::ORDER_STATUS_PAID;
            /** @var Order $order */
            $order = $this->orderRepository->create($orderAttributes);
            $paymentStatus = Payment::PAYMENT_STATUS_SUCCEEDED;
            $paymentMethodId = Payment::PAYMENT_METHOD_DEBIT;
            $this->purchaseService->createPayment($order, $paymentStatus, $description, $paymentMethodId);

            $users = $this->userRepository->getByListOfId($userIdList);
            foreach ($users as $user) {
                $this->userRepository->setModel($user);
                $this->userRepository->setWebinarChargedSuccessfully($webinar);
            }
        }
    }

    public function payForVisitor(Webinar $webinar, User $owner, User $user): bool
    {
        if ($owner->account->balance > Account::COST_PER_USER) {
            $description = 'Списание за посетителя ' . $user->id . ' вебинара №' . $webinar->id . ': ' . $webinar->getName();

            $balance = $owner->account->balance;
            $owner->account->balance = $balance - Account::COST_PER_USER;
            $owner->account->save();

            $orderAttributes = [];
            $orderAttributes['webinar_id'] = $webinar->id;
            $orderAttributes['user_id'] = $owner->account->users()->first()->id;
            $orderAttributes['amount'] = Account::COST_PER_USER;
            $orderAttributes['description'] = $description;
            $orderAttributes['status_id'] = OrderStatus::ORDER_STATUS_PAID;

            /** @var Order $order */
            $order = $this->orderRepository->create($orderAttributes);
            $this->purchaseService->createPayment($order, Payment::PAYMENT_STATUS_SUCCEEDED, $description, Payment::PAYMENT_METHOD_DEBIT, true);
            $owner->notify(new AccountUpdated($owner->account));

            $this->userRepository->setModel($user);
            $this->userRepository->setWebinarVisited($webinar);
            $this->userRepository->setWebinarChargedSuccessfully($webinar);

        } else {
            return false;
        }

        return true;
    }

    public function processTrialUser(Webinar $webinar, User $user)
    {
        if (!Setting::isTrialTypeTime()) {
            return;
        }

        $owner = $webinar->getOwner();
        $this->userRepository->setModel($user);

        if (!$webinar->isTrialUsersLimitReached() || $user->can('moderate', $webinar)) {
            $this->userRepository->setWebinarVisited($webinar);
            $this->userRepository->setWebinarChargedSuccessfully($webinar);
        } else {
            // Если триал неактивен или превышено количество бесплатных юзеров, то сразу снимаем оплату с аккаунта
            if ($owner->account->status != Account::STATUS_SUSPENDED) {
                $paymentResult = $this->payForVisitor($webinar, $owner, $user);

                if (!$paymentResult) {
                    if (!$owner->account->isTrialActive()) {
                        $owner->account->status = Account::STATUS_SUSPENDED;
                        $owner->account->save();
                    }

                    if (!$webinar->is_limit_reached_notified) {
                        $owner->notify(new UsersLimitReached($webinar));
                        $webinar->is_limit_reached_notified = true;
                        $webinar->save();
                    }
                }
            }
        }
    }

    /**
     * @param Account $account
     * @param float $amount
     * @return float|int
     */
    public function processTrialAmount(Account $account, $amount)
    {
        if (Setting::isTrialTypeTime()) {
            if ($account->isTrialActive()) {
                $amount = $amount * 2;
            }
        } else {
            if ($account->created_at->diffInHours(now()) <= 24) {
                $amount = $amount * 3;
            }
        }

        return $amount;
    }
}
