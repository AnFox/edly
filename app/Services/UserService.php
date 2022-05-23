<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Contracts\Services\UserServiceInterface;
use App\Exceptions\AttemptToRegisterAlreadyRegisteredUser;
use App\Models\User;
use App\Models\Webinar;
use App\Notifications\UserCreated;
use App\Repositories\UserRepositoryEloquent;
use Bouncer;
use Illion\Service\AuthService;
use Illion\Service\Models\User as IllionUser;
use Illion\Service\UserService as IllionUserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class UserService
 * @package App\Services
 */
class UserService implements UserServiceInterface
{
    /**
     * @var UserRepositoryEloquent
     */
    private $userRepository;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;
    /**
     * @var IllionUserService
     */
    private $illionUserService;
    /**
     * @var AuthService
     */
    private $illionAuthService;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     * @param WebinarRepository $webinarRepository
     * @param IllionUserService $illionUserService
     * @param AuthService $illionAuthService
     */
    public function __construct(UserRepository $userRepository,
                                WebinarRepository $webinarRepository,
                                IllionUserService $illionUserService,
                                AuthService $illionAuthService
    )
    {
        $this->userRepository = $userRepository;
        $this->webinarRepository = $webinarRepository;
        $this->illionUserService = $illionUserService;
        $this->illionAuthService = $illionAuthService;
    }

    public function createByEmail(string $email, string $defaultPassword = null)
    {
        $user = null;

        list($username, $domain) = explode('@', $email);

        $name = $first_name = $username;

        $password = $password_confirmation = $defaultPassword ?? Str::random(6);

        $role = 'visitor';

        // Unknown values
        $webinar = $last_name = $phone = null;
        $requires_phone_verification = false;

        $illionRegisteredUser = $this->illionUserService->register($name, $email, $password, $password_confirmation, $phone, $requires_phone_verification);

        if ($illionRegisteredUser) {
            $user = $this->create(
                $illionRegisteredUser->name,
                $illionRegisteredUser->email,
                $illionRegisteredUser->id,
                $phone,
                $first_name,
                $last_name,
                $role,
                $webinar
            );
            $user->notify(new UserCreated($password));
        }

        return $user;
    }

    /**
     * @param string $name
     * @param string $email
     * @param integer $external_id
     * @param string $phone
     * @param string $first_name
     * @param string $last_name
     * @param string $role
     * @param Webinar $webinar
     * @param null $email_verified_at
     * @return User
     */
    public function create(string $name, string $email, int $external_id, string $phone = null, string $first_name = null,
                           string $last_name = null, string $role = 'visitor', $webinar = null, $email_verified_at = null): User
    {
        $email_verified_at = $this->getVerificationDateForTestUser($email_verified_at, $webinar);

        /** @var User $user */
        $user = $this->userRepository->create([
            'external_id' => $external_id,
            'name' => $name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'email' => $email,
            'email_verified_at' => $email_verified_at,
        ]);

        $this->assignRoleToUser($role, $user);

        if ($webinar) {
            app(AccountService::class)->processTrialUser($webinar, $user);
        }

        return $user;
    }

    /**
     * For load test purposes
     * Test users have email auto verification when they register
     * To be recognized as test user he should register to a webinar owned by user from the list 'test.user_id_list'
     * and having slug 'loadtest'
     *
     * @param $email_verified_at
     * @param $webinar
     * @return \Illuminate\Support\Carbon
     */
    protected function getVerificationDateForTestUser($email_verified_at, $webinar)
    {
        /** @var Webinar $webinar */
        if (!$email_verified_at && $webinar) {
            if ($this->isTestWebinar($webinar)) {
                $email_verified_at = now();
            }
        }

        return $email_verified_at;
    }

    protected function updateUserFromIllion(User $user, IllionUser $illionUser): User
    {
        if ($user->external_id == $illionUser->id) {
            $user->email = $illionUser->email;
            $user->new_email = $illionUser->new_email;
            $user->email_verified_at = $illionUser->email_confirmed_at  ?: null;
            $user->phone = $illionUser->phone;
            $user->new_phone = $illionUser->new_phone;
            $user->phone_verified_at = $illionUser->phone_confirmed_at ?: null;

            if (isset($illionUser->socialNetworkAccounts)) {
                $user->social_network_accounts = $illionUser->socialNetworkAccounts->data ? json_encode($illionUser->socialNetworkAccounts->data, JSON_UNESCAPED_UNICODE) : null;
            }
        }

        return $user;
    }

    /**
     * @param User $user
     * @param array $params
     * @param IllionUser|null $illionUser
     * @return User
     * @throws Throwable
     */
    public function update(User $user, array $params, IllionUser $illionUser = null)
    {
        $user->fill($params);

        if ($illionUser) {
            $user = $this->updateUserFromIllion($user, $illionUser);
        }

        $user->saveOrFail();

        return $user;
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function findByExternalId(int $id): ?User
    {
        return $this->userRepository->findByExternalId($id);
    }

    public function handleUserLogout(): void
    {
        $this->userRepository->setModel(request()->user());
        $this->userRepository->setUserOfflineToVisitedWebinars();
    }

    /**
     * @param string $role
     * @param User $user
     */
    public function assignRoleToUser(string $role, User $user): void
    {
        switch ($role) {
            case 'owner':
                Bouncer::assign('owner')->to($user);
                $this->userRepository->setModel($user);
                if (!$this->userRepository->getFirstLinkedAccount()) {
                    $this->userRepository->createAccount();
                }
                break;
            case 'moderator':
                Bouncer::assign('moderator')->to($user);
                break;
            case 'visitor':
                Bouncer::assign('visitor')->to($user);
                break;
        }
    }

    /**
     * @param string $email
     * @return mixed
     */
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    /**
     * @param $webinar
     * @param string $role
     * @param string $email
     * @param string|null $phone
     * @param bool $requires_phone_verification
     * @param bool $requires_email_verification
     * @param string|null $defaultPassword
     * @return array
     * @throws AttemptToRegisterAlreadyRegisteredUser
     */
    public function autologinByEmail($webinar, string $role, string $email, $phone, bool $requires_phone_verification, bool $requires_email_verification, string $defaultPassword = null): array
    {
        if ($this->findByEmail($email)) {
            throw new AttemptToRegisterAlreadyRegisteredUser();
        }

        list($username, $domain) = explode('@', $email);

        $name = $username;
        $first_name = $last_name = null;

        $password = $password_confirmation = $defaultPassword ?? Str::random(6);

        $userServiceUser = $this->illionUserService->register(
            $name, $email, $password, $password_confirmation, $phone,
            $requires_phone_verification, $requires_email_verification
        );

        $user = $this->create(
            $userServiceUser->name,
            $userServiceUser->email,
            $userServiceUser->id,
            $userServiceUser->phone,
            $first_name,
            $last_name,
            $role,
            $webinar
        );

        Auth::guard('api')->setUser($user);

        $token = $this->illionAuthService->attemptLogin($email, $password, true);

        return [$user, $token];
    }

    /**
     * @param $webinar
     * @return bool
     */
    public function isTestWebinar(Webinar $webinar): bool
    {
        $ownerTestableIdList = explode(',', config('test.user_id_list'));
        $isTestWebinar = $webinar->room->slug == 'loadtest' && in_array($webinar->room->user_id, $ownerTestableIdList);

        return $isTestWebinar;
    }
}
