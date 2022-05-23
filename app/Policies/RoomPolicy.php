<?php

namespace App\Policies;

use App\Contracts\Repositories\AccountRepository;
use App\Contracts\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * Class RoomPolicy
 * @package App\Policies
 */
class RoomPolicy
{
    use HandlesAuthorization;

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * WebinarPolicy constructor.
     * @param UserRepository $userRepository
     * @param AccountRepository $accountRepository
     */
    public function __construct(UserRepository $userRepository, AccountRepository $accountRepository)
    {
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param User $user
     * @return Response
     */
    public function create(User $user)
    {
        $account = $this->userRepository->setModel($user)->getFirstLinkedAccount();
        $this->accountRepository->setModel($account);

        return $this->accountRepository->canCreateRoom()
            ? Response::allow()
            : Response::deny(__('auth.account_has_limitations'));
    }
}
