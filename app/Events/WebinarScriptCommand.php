<?php

namespace App\Events;

use App\Http\Resources\ScriptCommandResource;
use App\Models\Script;
use App\Models\Webinar;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Class WebinarScriptCommand
 * @package App\Events
 */
class WebinarScriptCommand implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;
    /**
     * @var Webinar
     */
    private $webinar;
    /**
     * @var Script
     */
    private $command;

    /**
     * Create a new event instance.
     *
     * @param Webinar $webinar
     * @param Script $command
     */
    public function __construct(Webinar $webinar, Script $command)
    {
        $this->webinar = $webinar;
        $this->command = $command;
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
        return 'WebinarScriptCommand';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return (new ScriptCommandResource($this->command))->resolve();
    }
}
