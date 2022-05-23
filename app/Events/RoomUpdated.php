<?php

namespace App\Events;

use App\Http\Resources\RoomWebsocketResource;
use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class RoomUpdated
 * @package App\Events
 */
class RoomUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @var Room
     */
    private $room;

    /**
     * Create a new event instance.
     *
     * @param Room $room
     */
    public function __construct(Room $room)
    {
        $this->room = $room;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('room.' . $this->room->id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'RoomUpdated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return (new RoomWebsocketResource($this->room))->resolve();
    }
}
