<?php

namespace App\Listeners;

use App\Contracts\Repositories\RoomRepository;
use App\Contracts\Repositories\ScriptRepository;
use App\Events\NewRecordableAction;
use App\Models\Room;
use App\Models\Script;
use App\Models\Webinar;
use Log;

class RecordScriptCommand
{
    /**
     * @var ScriptRepository
     */
    private $scriptRepository;
    /**
     * @var RoomRepository
     */
    private $roomRepository;

    /**
     * Create the event listener.
     *
     * @param ScriptRepository $scriptRepository
     * @param RoomRepository $roomRepository
     */
    public function __construct(ScriptRepository $scriptRepository, RoomRepository $roomRepository)
    {
        $this->scriptRepository = $scriptRepository;
        $this->roomRepository = $roomRepository;
    }

    /**
     * Handle the event.
     *
     * @param NewRecordableAction $event
     * @return void
     */
    public function handle(NewRecordableAction $event)
    {
        if ($event->webinar->room->type_id === Room::TYPE_LIVE && $event->webinar->is_recordable) {
            $supportedActions = [
                Script::ACTION_START_RECORD,
                Script::ACTION_STOP_RECORD,
                Script::ACTION_START_STREAM,
                Script::ACTION_STOP_STREAM,
                Script::ACTION_WEBINAR_LAYOUT,
                Script::ACTION_WEBINAR_TAB,
                Script::ACTION_SET_PRESENTATION_PAGE,
                Script::ACTION_POST_MESSAGE,
                Script::ACTION_POST_BANNER,
                Script::ACTION_CHAT_BLOCK,
                Script::ACTION_CHAT_UNBLOCK,
            ];

            if (in_array($event->action, $supportedActions)) {
                $timeshift = $this->getTimeshift($event->webinar);

                $this->scriptRepository->create([
                    'room_id' => $event->webinar->room->id,
                    'action' => $event->action,
                    'payload' => json_encode($event->payload),
                    'timeshift' => $timeshift,
                ]);

                if ($event->action === Script::ACTION_STOP_RECORD) {
                    $this->roomRepository->setModel($event->webinar->room);
                    $this->roomRepository->extendDurationIfNeeded($timeshift);
                }
            }
        }
    }

    private function getTimeshift(Webinar $webinar)
    {
        return $webinar->starts_at->diffInMilliseconds(now());
    }
}
