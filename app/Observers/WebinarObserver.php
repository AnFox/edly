<?php

namespace App\Observers;

use App\Contracts\Repositories\WebinarRepository;
use App\Events\WebinarUpdated;
use App\Models\Webinar;

class WebinarObserver
{
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    public function __construct(WebinarRepository $webinarRepository)
    {
        $this->webinarRepository = $webinarRepository;
    }

    /**
     * Handle the webinar "updated" event.
     *
     * @param  \App\Models\Webinar  $webinar
     * @return void
     */
    public function updated(Webinar $webinar)
    {
        event(new WebinarUpdated($webinar));

        // Create next scheduled webinar on webinar finish
        if ($webinar->isDirty('finished_at')) {
            if ($webinar->is_scheduled) {
                $this->webinarRepository->createScheduled($webinar->room->id, $webinar->starts_at, $webinar->room->schedule_interval);
            }
        }
    }
}
