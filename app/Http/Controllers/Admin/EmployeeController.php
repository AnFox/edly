<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EmployeeCreateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illion\Service\UserService as IllionUserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class UserController
 * @package App\Http\Controllers\Admin
 */
class EmployeeController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var IllionUserService
     */
    private $illionUserService;

    /**
     * EmployeeController constructor.
     * @param UserRepository $userRepository
     * @param UserService $userService
     * @param IllionUserService $illionUserService
     */
    public function __construct(UserRepository $userRepository, UserService $userService, IllionUserService $illionUserService)
    {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->illionUserService = $illionUserService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('view-employee', $this->userRepository->getClass());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EmployeeCreateRequest $request
     * @return UserResource
     * @throws AuthorizationException
     */
    public function store(EmployeeCreateRequest $request): UserResource
    {
        $this->authorize('create-employee', $this->userRepository->getClass());

        /** @var User $user */
        $attributes = $request->validated();
        $attributes['name'] = $attributes['first_name'] . ' ' . $attributes['last_name'];

        // Create user
        $email = $request->get('email');
        $password = $request->get('password');
        $password_confirmation = $password;
        $phone = $request->get('phone');
        $first_name = $request->get('first_name');
        $last_name = $request->get('last_name');
        $name = $first_name . ' ' . $last_name;

        $role = $request->get('role');

        $requires_phone_verification = false;

        $illionRegisteredUser = $this->illionUserService->register($name, $email, $password, $password_confirmation, $phone, $requires_phone_verification);

        if ($illionRegisteredUser) {
            $user = $this->userService->create(
                $illionRegisteredUser->name,
                $illionRegisteredUser->email,
                $illionRegisteredUser->id,
                $phone,
                $first_name,
                $last_name,
                $role
            );
        }

        // Link user to account
        $account = $request->user()->linkedAccounts->first();
        $this->userRepository->setModel($user);
        $this->userRepository->setLinkedAccount($account);

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws AuthorizationException
     */
    public function update(Request $request, $id)
    {
        $this->authorize('update-employee', $this->userRepository->getClass());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $this->authorize('delete-employee', $this->userRepository->getClass());
    }
}
