<?php

namespace App\Events;

use App\Models\Webinar;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class WebinarLayoutChange
 * @package App\Events
 */
class WebinarLayoutChange implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;
    /**
     * @var Webinar
     */
    private $webinar;
    /**
     * @var string
     */
    private $layout;

    /**
     * Create a new event instance.
     *
     * @param Webinar $webinar
     * @param string $layout
     */
    public function __construct(Webinar $webinar, string $layout)
    {
        $this->webinar = $webinar;
        $this->layout = $layout;
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
        return 'WebinarLayoutChange';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->webinar->id,
            'layout' => $this->layout,
        ];
    }
}
