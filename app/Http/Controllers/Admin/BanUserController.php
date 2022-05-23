<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BanUserRequest;
use App\Models\Room;
use App\Models\User;
use App\Notifications\WebinarAccessAllowed;
use App\Notifications\WebinarAccessForbidden;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Class BanUserController
 * @package App\Http\Controllers\Admin
 */
class BanUserController extends Controller
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * BanUserController constructor.
     * @param UserRepository $userRepository
     * @param WebinarRepository $webinarRepository
     */
    public function __construct(UserRepository $userRepository, WebinarRepository $webinarRepository)
    {
        $this->userRepository = $userRepository;
        $this->webinarRepository = $webinarRepository;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param int $webinarId
     * @param int $userId
     * @return void
     * @throws AuthorizationException
     */
    public function store(int $webinarId, int $userId)
    {
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        /** @var Room $room */
        $room = $webinar->room;
        $this->authorize('moderate', $room);

        /** @var User $user */
        $user = $this->userRepository->find($userId)->getModel();
        $user->forbid(User::PERMISSION_ABILITY_VIEW_WEBINAR_OWNED_BY, $room->owner);
        $this->userRepository->setBannedOnWebinar($webinar);
        $user->notify(new WebinarAccessForbidden($webinar));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $webinarId
     * @param int $userId
     * @return void
     * @throws AuthorizationException
     */
    public function destroy(int $webinarId, int $userId)
    {
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        /** @var Room $room */
        $room = $webinar->room;
        $this->authorize('moderate', $room);

        /** @var User $user */
        $user = $this->userRepository->find($userId)->getModel();
        $user->unforbid(User::PERMISSION_ABILITY_VIEW_WEBINAR_OWNED_BY, $room->owner);
        $this->userRepository->setActiveOnWebinar($webinar);
        $user->notify(new WebinarAccessAllowed($webinar));
    }
}
