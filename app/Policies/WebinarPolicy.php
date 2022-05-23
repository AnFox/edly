<?php

namespace App\Policies;

use App\Contracts\Repositories\AccountRepository;
use App\Contracts\Repositories\UserRepository;
use App\Models\User;
use App\Models\Webinar;
use Bouncer;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * Class AdminWebinarPolicy
 * @package App\Policies
 */
class WebinarPolicy
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

        return $this->accountRepository->canCreateWebinar()
            ? Response::allow()
            : Response::deny(__('auth.account_has_limitations'));
    }

    /**
     * @param User $user
     * @param Webinar $webinar
     * @return bool
     */
    public function moderate(User $user, Webinar $webinar): bool
    {
        return (bool)array_intersect($user->linkedAccounts->pluck('id')->toArray(), $webinar->room->owner->linkedAccounts->pluck('id')->toArray());
    }

    /**
     * @param Webinar $webinar
     * @return bool
     */
    public function view(Webinar $webinar): bool
    {
        $webinarOwnerAccount = $webinar->room->owner->linkedAccounts()->first();

        return Bouncer::can('access-account-entities', $webinarOwnerAccount);
    }
}
