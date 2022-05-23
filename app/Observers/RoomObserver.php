<?php

namespace App\Observers;

use App\Contracts\Repositories\WebinarRepository;
use App\Events\RoomUpdated;
use App\Events\WebinarUpdated;
use App\Models\Room;
use App\Models\Webinar;

class RoomObserver
{
    /**
     * Handle the room "updated" event.
     *
     * @param  \App\Models\Room  $room
     * @return void
     */
    public function updated(Room $room)
    {
        event(new RoomUpdated($room));

        if ($room->isDirty('scheduled_at')) {
            // Find future webinars (with scheduled time equal to original scheduled_at?)
            $webinars = $room->webinars()
                ->where('is_scheduled', true)
                ->where('starts_at', '>=', now())
//                ->whereTime('starts_at', '=', $room->getOriginal('scheduled_at'))
                ->get();

            /** @var Webinar $webinar */
            foreach ($webinars as $webinar) {
                if ($room->scheduled_at->gte(now())) {
                    $webinar->starts_at = $room->scheduled_at;
                } else {
                    // Workaround for the cases with scheduled_at in the past
                    $webinar->starts_at = $webinar->starts_at->setTimeFrom($room->scheduled_at);
                }
                $webinar->save();
            }

            if (!$webinars->count()) {
                app(WebinarRepository::class)->create([
                    'is_scheduled' => true,
                    'room_id' => $room->id,
                    'starts_at' => $room->scheduled_at
                ]);
            }
        }

        $webinars = $room->webinars;
        foreach ($webinars as $webinar) {
            event(new WebinarUpdated($webinar));
        }
    }
}
