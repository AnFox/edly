<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illion\Service\UserService as IllionUserService;
use Illuminate\Http\Request;
use Throwable;

class UserController extends Controller
{
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var IllionUserService
     */
    private $illionUserService;

    public function __construct(UserService $userService, IllionUserService $illionUserService)
    {
        $this->userService = $userService;
        $this->illionUserService = $illionUserService;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @return UserResource
     */
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserUpdateRequest $request
     * @return UserResource
     * @throws Throwable
     */
    public function update(UserUpdateRequest $request)
    {
        $token = $request->bearerToken();
        $attributes = $request->validated();

        if (count($attributes) === 1 && array_key_exists('first_name', $attributes)) {
            $user = $this->userService->update($request->user(), $attributes);

            return new UserResource($user);
        }

        $attributes['verification_password'] = $request->get('password_current');

        $illionUser = $this->illionUserService->update($token, $attributes);

        $user = $this->userService->update($request->user(), $attributes, $illionUser);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return void
     */
    public function destroy(Request $request): void
    {
        $user = $request->user();
        $user->delete();
    }
}
