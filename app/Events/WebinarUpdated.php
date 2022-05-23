<?php

namespace App\Events;

use App\Http\Resources\WebinarWebsocketResource;
use App\Models\Webinar;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class WebinarUpdated
 * @package App\Events
 */
class WebinarUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;
    /**
     * @var Webinar
     */
    private $webinar;

    /**
     * Create a new event instance.
     *
     * @param Webinar $webinar
     */
    public function __construct(Webinar $webinar)
    {
        $this->webinar = $webinar;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('webinar.' . $this->webinar->id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'WebinarUpdated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return (new WebinarWebsocketResource($this->webinar))->resolve();
    }
}
