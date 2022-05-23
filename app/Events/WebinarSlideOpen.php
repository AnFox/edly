<?php

namespace App\Events;

use App\Http\Resources\SlideResource;
use App\Models\Webinar;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class WebinarSlideOpen
 * @package App\Events
 */
class WebinarSlideOpen implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;
    /**
     * @var Webinar
     */
    private $webinar;
    /**
     * @var Media
     */
    private $slide;

    /**
     * Create a new event instance.
     *
     * @param Webinar $webinar
     * @param Media $slide
     */
    public function __construct(Webinar $webinar, Media $slide)
    {
        $this->webinar = $webinar;
        $this->slide = $slide;
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
        return 'WebinarSlideOpen';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return (new SlideResource($this->slide))->resolve();
    }
}
