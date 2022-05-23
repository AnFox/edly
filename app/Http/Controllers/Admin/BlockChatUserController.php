<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\WebinarRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UnblockUsersRequest;
use App\Http\Resources\Admin\WebinarResource;
use App\Notifications\WebinarChatBlocked;
use App\Notifications\WebinarChatUnblocked;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Class BlockChatUserController
 * @package App\Http\Controllers\Admin
 */
class BlockChatUserController extends Controller
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
    public function store(int $webinarId, int $userId): void
    {
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        $room = $webinar->room;
        $this->authorize('moderate', $room);

        $user = $this->userRepository->find($userId)->getModel();
        $user->forbid('post-message', $webinar);
        $this->userRepository->setChatBlockedOnWebinar($webinar);
        $user->notify(new WebinarChatBlocked($webinar));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $webinarId
     * @param int $userId
     * @return WebinarResource
     * @throws AuthorizationException
     */
    public function destroy(int $webinarId, int $userId): WebinarResource
    {
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        $room = $webinar->room;
        $this->authorize('moderate', $room);

        $user = $this->userRepository->find($userId)->getModel();
        $user->unforbid('post-message', $webinar);
        $this->userRepository->setActiveOnWebinar($webinar);
        $user->notify(new WebinarChatUnblocked($webinar));

        return new WebinarResource($webinar);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $webinarId
     * @return WebinarResource
     * @throws AuthorizationException
     */
    public function destroyAll(int $webinarId): WebinarResource
    {
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        $this->authorize('moderate', $webinar->room);

        $users = $this->webinarRepository->getBlockedVisitors();
        foreach ($users as $user) {
            $user->unforbid('post-message', $webinar);
            $this->userRepository->setModel($user)->setActiveOnWebinar($webinar);
            $user->notify(new WebinarChatUnblocked($webinar));
        }

        return new WebinarResource($webinar->fresh());
    }

    /**
     * @param int $webinarId
     * @param UnblockUsersRequest $request
     * @return WebinarResource
     * @throws AuthorizationException
     */
    public function destroyFromList(int $webinarId, UnblockUsersRequest $request): WebinarResource
    {
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        $this->authorize('moderate', $webinar->room);

        if ($idList = $request->get('idList')) {
            $users = $this->webinarRepository->getVisitorsByIdList($idList);
            foreach ($users as $user) {
                $user->unforbid('post-message', $webinar);
                $this->userRepository->setModel($user)->setActiveOnWebinar($webinar);
                $user->notify(new WebinarChatUnblocked($webinar));
            }

            return new WebinarResource($webinar->fresh());
        }
    }
}
