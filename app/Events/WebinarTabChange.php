<?php

namespace App\Events;

use App\Models\Webinar;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class WebinarTabChange
 * @package App\Events
 */
class WebinarTabChange implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;
    /**
     * @var Webinar
     */
    private $webinar;
    /**
     * @var string
     */
    private $tab;

    /**
     * Create a new event instance.
     *
     * @param Webinar $webinar
     * @param string $tab
     */
    public function __construct(Webinar $webinar, string $tab)
    {
        $this->webinar = $webinar;
        $this->tab = $tab;
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
        return 'WebinarTabChange';
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
            'tab' => $this->tab,
        ];
    }
}
