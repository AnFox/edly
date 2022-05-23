<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\AccountRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountSetPaymentRequest;
use App\Http\Requests\Admin\AccountSetPixelRequest;
use App\Http\Resources\Admin\AccountResource;
use App\Http\Resources\UserResource;
use App\Models\Account;
use Illuminate\Http\Request;

/**
 * Class AccountController
 * @package App\Http\Controllers\Admin
 */
class AccountController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * AccountController constructor.
     * @param UserRepository $userRepository
     * @param AccountRepository $accountRepository
     */
    public function __construct(UserRepository $userRepository, AccountRepository $accountRepository)
    {
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param Request $request
     * @return UserResource
     */
    public function deleteCard(Request $request): UserResource
    {
        $account = $this->userRepository->setModel($request->user())->getFirstLinkedAccount();
        $this->accountRepository->setModel($account)->deleteCard();

        return new UserResource($request->user());
    }

    public function setPaymentSettings(AccountSetPaymentRequest $request)
    {
        /** @var Account $account */
        $account = $this->userRepository->setModel($request->user())->getFirstLinkedAccount();

        if ($request->get('cashbox_system') === 'ATOL') {
            $account->setOption('cashbox', [
                'system' => $request->get('cashbox_system'),
                'inn' => $request->get('cashbox_inn'),
                'login' => $request->get('cashbox_login'),
                'password' => $request->get('cashbox_password'),
                'group' => $request->get('cashbox_group'),
            ]);
        } else {
            $account->setOption('cashbox', [
                'system' => $request->get('cashbox_system'),
            ]);
        }

        $account->setOption('payment', [
            $request->get('payment_system') => [
                'public_key' => $request->get('payment_system_public_key'),
                'private_key' => $request->get('payment_system_private_key'),
            ],
        ]);

        return new AccountResource($account);
    }

    public function setPixelSettings(AccountSetPixelRequest $request)
    {
        /** @var Account $account */
        $account = $this->userRepository->setModel($request->user())->getFirstLinkedAccount();

        $account->setOption('fb_pixel', $request->get('fb_pixel'));

        return new AccountResource($account);
    }

    public function unsetPixelSettings(Request $request)
    {
        /** @var Account $account */
        $account = $this->userRepository->setModel($request->user())->getFirstLinkedAccount();

        $account->unsetOption('fb_pixel');

        return new AccountResource($account);
    }
}
