<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\Webinar;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class WebinarChatUnblockedForEveryone
 * @package App\Events
 */
class WebinarChatUnblockedForEveryone implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;
    /**
     * @var Webinar
     */
    private $webinar;

    /**
     * @var Chat
     */
    private $chat;

    /**
     * Create a new event instance.
     *
     * @param Chat $chat
     * @param Webinar $webinar
     */
    public function __construct(Chat $chat, Webinar $webinar)
    {
        $this->chat = $chat;
        $this->webinar = $webinar;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('chat.' . $this->chat->id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'WebinarChatUnblockedForEveryone';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return ['id' => $this->webinar->id, 'chat_enabled' => (bool) $this->chat->is_active];
    }
}
