<?php

namespace App\Observers;

use App\Events\ChatMessageDeleted;
use App\Events\NewChatMessage;
use App\Events\NewRecordableAction;
use App\Models\ChatMessage;
use App\Models\Script;

/**
 * Class ChatMessageObserver
 * @package App\Observers
 */
class ChatMessageObserver
{
    /**
     * @param ChatMessage $chatMessage
     */
    public function created(ChatMessage $chatMessage)
    {
        event(new NewChatMessage($chatMessage));

        if ($chatMessage->banner_id) {
            event(new NewRecordableAction($chatMessage->chat->webinar, Script::ACTION_POST_BANNER, [
                'id' => $chatMessage->banner_id,
            ]));
        } else {
            if ($chatMessage->sender) {
                event(new NewRecordableAction($chatMessage->chat->webinar, Script::ACTION_POST_MESSAGE, [
                    'role' => $chatMessage->sender->isAn('owner') ? 'admin' : 'guest',
                    'username' => $chatMessage->sender->name,
                    'message' => $chatMessage->message,
                ]));
            }
        }
    }

    /**
     * @param ChatMessage $chatMessage
     */
    public function deleted(ChatMessage $chatMessage)
    {
        event(new ChatMessageDeleted($chatMessage));
    }
}
