<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class ChatMessageDeleted
 * @package App\Events
 */
class ChatMessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;
    /**
     * @var ChatMessage
     */
    private $chatMessage;

    /**
     * Create a new event instance.
     *
     * @param ChatMessage $chatMessage
     */
    public function __construct(ChatMessage $chatMessage)
    {
        $this->chatMessage = $chatMessage;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('chat.' . $this->chatMessage->chat_id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'ChatMessageDeleted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [$this->chatMessage->id];
    }
}
