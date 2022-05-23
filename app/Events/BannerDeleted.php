<?php

namespace App\Events;

use App\Models\Banner;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class BannerDeleted
 * @package App\Events
 */
class BannerDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;
    /**
     * @var Banner
     */
    private $banner;

    /**
     * Create a new event instance.
     *
     * @param Banner $banner
     */
    public function __construct(Banner $banner)
    {
        $this->banner = $banner;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('webinar.' . $this->banner->webinar->id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'BannerDeleted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [$this->banner->id];
    }
}
