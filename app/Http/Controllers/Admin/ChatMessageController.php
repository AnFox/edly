<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\ChatMessageRepository;
use App\Contracts\Repositories\ChatRepository;
use App\Contracts\Repositories\UserRepository;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteChatMessageRequest;
use App\Models\User;
use App\Notifications\WebinarAccessForbidden;
use App\Notifications\WebinarChatBlocked;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Class ChatMessageController
 * @package App\Http\Controllers\Admin
 */
class ChatMessageController extends Controller
{
    /**
     * @var ChatMessageRepository
     */
    private $chatMessageRepository;
    /**
     * @var ChatRepository
     */
    private $chatRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ChatMessageController constructor.
     * @param ChatMessageRepository $chatMessageRepository
     * @param ChatRepository $chatRepository
     * @param UserRepository $userRepository
     */
    public function __construct(ChatMessageRepository $chatMessageRepository, ChatRepository $chatRepository,
                                UserRepository $userRepository)
    {
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatRepository = $chatRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param int $chatId
     * @param DeleteChatMessageRequest $request
     * @throws AuthorizationException
     */
    public function delete(int $chatId, DeleteChatMessageRequest $request)
    {
        $webinar = $this->chatRepository->find($chatId)->getWebinar();
        $this->authorize('moderate', $webinar->room);

        if ($idList = $request->get('idList')) {
            $this->chatMessageRepository->deleteMessages($idList, $chatId);
        }
    }

    /**
     * @param int $chatId
     * @param DeleteChatMessageRequest $request
     * @throws AuthorizationException
     */
    public function blockUsers(int $chatId, DeleteChatMessageRequest $request)
    {
        $webinar = $this->chatRepository->find($chatId)->getWebinar();
        $this->authorize('moderate', $webinar->room);

        if ($idList = $request->get('idList')) {
            $messages = $this->chatMessageRepository->getByIdList($idList);
            foreach ($messages as $message) {
                $user = $message->sender;
                $this->userRepository->setModel($user);
                $this->userRepository->setChatBlockedOnWebinar($webinar);
                $user->forbid('post-message', $webinar);
                $user->notify(new WebinarChatBlocked($webinar));
            }
        }
    }

    /**
     * @param int $chatId
     * @param DeleteChatMessageRequest $request
     * @throws AuthorizationException
     */
    public function banUsers(int $chatId, DeleteChatMessageRequest $request)
    {
        $webinar = $this->chatRepository->find($chatId)->getWebinar();
        $this->authorize('moderate', $webinar->room);

        if ($idList = $request->get('idList')) {
            $messages = $this->chatMessageRepository->getByIdList($idList);
            foreach ($messages as $message) {
                if ($message->chat_id === $chatId) {
                    $user = $message->sender;
                    $this->userRepository->setModel($user);
                    $this->userRepository->setBannedOnWebinar($webinar);
                    $user->forbid(User::PERMISSION_ABILITY_VIEW_WEBINAR_OWNED_BY, $webinar->room->owner);
                    $user->notify(new WebinarAccessForbidden($webinar));
                }
            }
        }
    }

    /**
     * @param int $chatId
     * @param DeleteChatMessageRequest $request
     * @throws AuthorizationException
     */
    public function banUsersAndDeleteMessages(int $chatId, DeleteChatMessageRequest $request)
    {
        $webinar = $this->chatRepository->find($chatId)->getWebinar();
        $this->authorize('moderate', $webinar->room);

        if ($idList = $request->get('idList')) {
            $messages = $this->chatMessageRepository->getByIdList($idList);
            foreach ($messages as $message) {
                if ($message->chat_id === $chatId) {
                    $user = $message->sender;
                    $this->userRepository->setModel($user);
                    $this->userRepository->setBannedOnWebinar($webinar);
                    $user->forbid(User::PERMISSION_ABILITY_VIEW_WEBINAR_OWNED_BY, $webinar->room->owner);
                    $user->notify(new WebinarAccessForbidden($webinar));
                }
            }
            $this->chatMessageRepository->deleteMessages($idList, $chatId);
        }
    }
}
