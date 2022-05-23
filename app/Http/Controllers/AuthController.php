<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\WebinarRepository;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthNetworkRequest;
use App\Http\Requests\AuthRefreshRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Requests\CheckEmailRequest;
use App\Http\Requests\PasswordForgotRequest;
use App\Http\Resources\TokenResource;
use App\Models\User;
use App\Notifications\MustVerifyPhone;
use App\Notifications\UserCreated;
use App\Services\UserService;
use Bouncer;
use Carbon\Carbon;
use Illion\Service\AuthService;
use Illion\Service\TokenService;
use Illion\Service\UserService as IllionUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


/**
 * Class AuthController
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    private $illionAuthService;
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var IllionUserService
     */
    private $illionUserService;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * AuthController constructor.
     * @param AuthService $illionAuthService
     * @param UserService $userService
     * @param IllionUserService $illionUserService
     * @param WebinarRepository $webinarRepository
     */
    public function __construct(
        AuthService $illionAuthService,
        UserService $userService,
        IllionUserService $illionUserService,
        WebinarRepository $webinarRepository
    )
    {
        $this->illionAuthService = $illionAuthService;
        $this->userService = $userService;
        $this->illionUserService = $illionUserService;
        $this->webinarRepository = $webinarRepository;
    }

    /**
     * @param AuthLoginRequest $authLoginRequest
     * @return TokenResource
     */
    public function login(AuthLoginRequest $authLoginRequest): TokenResource
    {
        $email = $authLoginRequest->get('email');
        $phone = $authLoginRequest->get('phone');
        $username = $email ?? $phone;
        $password = $authLoginRequest->get('password');
        $rememberMe = (bool)$authLoginRequest->get('remember_me');

        $token = $this->illionAuthService->attemptLogin($username, $password, $rememberMe);

        $tokenService = new TokenService($token->access_token);
        $user = $this->userService->findByExternalId($tokenService->getUserId());

        if (!$user) {
            $illionUser = $this->illionUserService->show($token->access_token);

            $this->userService->create(
                $illionUser->name,
                $illionUser->email,
                $illionUser->id
            );
        }

        return new TokenResource($token);
    }

    /**
     * @param AuthRegisterRequest $authLoginRequest
     * @return TokenResource
     * @throws \App\Exceptions\AttemptToRegisterAlreadyRegisteredUser
     */
    public function register(AuthRegisterRequest $authLoginRequest): TokenResource
    {
        $email = $authLoginRequest->get('email');
        $phone = $authLoginRequest->get('phone');
        $webinar = null;
        $role = 'owner';

        if ($intendedUrl = $authLoginRequest->get('intendedUrl', false)) {
            $webinar = $this->webinarRepository->findByIntendedUrl($intendedUrl);
            if ($webinar) {
                $role = 'visitor';
            }
        }

        $requires_phone_verification = User::OWNER_MUST_VERIFY_PHONE;
        $requires_email_verification = true;

        if ($webinar && $this->userService->isTestWebinar($webinar)) {
            $requires_phone_verification = false;
            $requires_email_verification = false;
            $phone = null;
        }

        list($user, $token) = $this
            ->userService
            ->autologinByEmail($webinar, $role, $email, $phone, $requires_phone_verification, $requires_email_verification);

        if ($requires_phone_verification) {
            $user->notify(new MustVerifyPhone());
        }

        return new TokenResource($token);
    }

    /**
     * @param AuthRefreshRequest $authRefreshRequest
     * @return TokenResource
     */
    public function refresh(AuthRefreshRequest $authRefreshRequest): TokenResource
    {
        $refreshToken = $authRefreshRequest->get('refresh_token');
        $token = $this->illionAuthService->attemptRefresh($refreshToken);

        return new TokenResource($token);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $value = $request->bearerToken();
        $this->illionAuthService->logout($value);
        $this->userService->handleUserLogout();
        Auth::guard('api')->logout();

        return response()->json();
    }

    /**
     * @param CheckEmailRequest $request
     * @return Response
     */
    public function checkEmail(CheckEmailRequest $request): Response
    {
        $email = $request->input('email');

        return new Response([
            'data' => [
                'allow' => $this->illionUserService->checkEmail($email),
            ],
        ]);
    }

    /**
     * @param PasswordForgotRequest $request
     * @return JsonResponse
     */
    public function passwordForgot(PasswordForgotRequest $request): JsonResponse
    {
        $email = $request->get('email');
        $this->illionUserService->passwordForgot($email);

        return response()->json();
    }

    /**
     * @param AuthNetworkRequest $authLoginRequest
     * @return TokenResource
     */
    public function network(AuthNetworkRequest $authLoginRequest): TokenResource
    {
        $password = Str::random(6);
        $token = $authLoginRequest->get('token');
        $rememberMe = (bool) $authLoginRequest->get('remember_me');
        $socialNetworkName = $authLoginRequest->get('social_network_name');

        $token = $this->illionAuthService->attemptNetworkLogin($token, $rememberMe, $socialNetworkName, $password);

        $tokenService = new TokenService($token->access_token);
        $user = $this->userService->findByExternalId($tokenService->getUserId());

        if (!$user) {
            $webinar = $phone = null;
            $role = 'owner';

            if ($intendedUrl = $authLoginRequest->get('intendedUrl', false)) {
                $webinar = $this->webinarRepository->findByIntendedUrl($intendedUrl);
                if ($webinar) {
                    $role = 'visitor';
                }
            }

            $illionUser = $this->illionUserService->show($token->access_token);

            $email_verified_at = $illionUser->email_confirmed_at ?: Carbon::now();

            $user = $this->userService->create(
                $illionUser->name,
                $illionUser->email,
                $illionUser->id,
                $phone,
                null,
                null,
                $role,
                $webinar,
                $email_verified_at
            );

            $user->notify(new UserCreated($password));

            if (!$illionUser->email_confirmed_at) {
                $attributes = [];
                $attributes['email_confirmed_at'] = $user->email_verified_at->toDateTimeString();
                $this->illionUserService->update($token->access_token, $attributes);
            }
        } else {
            // Confirm user email if it's not yet confirmed since we trust social networks
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
                $user->save();
                $this->illionUserService->update($token->access_token, ['email_confirmed_at' => $user->email_verified_at->toDateTimeString()]);
            }
        }

        return new TokenResource($token);
    }
}
