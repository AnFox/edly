<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\ChatRepository;
use App\Events\NewRecordableAction;
use App\Events\WebinarChatBlockedForEveryone;
use App\Events\WebinarChatUnblockedForEveryone;
use App\Http\Controllers\Controller;
use App\Models\Script;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Class ChatController
 * @package App\Http\Controllers\Admin
 */
class ChatController extends Controller
{
    /**
     * @var ChatRepository
     */
    private $chatRepository;

    /**
     * ChatController constructor.
     * @param ChatRepository $chatRepository
     */
    public function __construct(ChatRepository $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    /**
     * Block chat for everyone
     *
     * @param int $chatId
     * @throws AuthorizationException
     */
    public function block(int $chatId): void
    {
        $chat = $this->chatRepository->find($chatId);
        $webinar = $chat->getWebinar();
        $this->authorize('block-chat', $webinar->room);

        $chat->block();
        event(new WebinarChatBlockedForEveryone($chat->getModel(), $webinar));
        event(new NewRecordableAction($webinar, Script::ACTION_CHAT_BLOCK));

    }

    /**
     * Unblock chat for everyone
     *
     * @param int $chatId
     * @throws AuthorizationException
     */
    public function unblock(int $chatId): void
    {
        $chat = $this->chatRepository->find($chatId);
        $webinar = $chat->getWebinar();
        $this->authorize('unblock-chat', $webinar->room);

        $chat->unblock();
        event(new WebinarChatUnblockedForEveryone($chat->getModel(), $webinar));
        event(new NewRecordableAction($webinar, Script::ACTION_CHAT_UNBLOCK));
    }
}
