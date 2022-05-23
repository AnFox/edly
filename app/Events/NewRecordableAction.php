<?php

namespace App\Events;

use App\Models\Room;
use App\Models\Script;
use App\Models\Webinar;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRecordableAction
{
    use Dispatchable, SerializesModels;
    /**
     * @var Room
     */
    public $webinar;
    /**
     * @var Script
     */
    public $action;
    /**
     * @var array
     */
    public $payload;

    /**
     * Create a new event instance.
     *
     * @param Webinar $webinar
     * @param string $action
     * @param array $payload
     */
    public function __construct(Webinar $webinar, string $action, array $payload = [])
    {
        $this->webinar = $webinar;
        $this->action = $action;
        $this->payload = $payload;
    }

}
