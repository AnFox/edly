<?php

namespace App\Jobs;

use App\Contracts\Repositories\WebinarRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class RunAutoWebinars
 * @package App\Jobs
 */
class RunAutoWebinars implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @param WebinarRepository $webinarRepository
     * @return void
     */
    public function handle(WebinarRepository $webinarRepository)
    {
        $webinars = $webinarRepository->getCurrentAutoWebinars();
        foreach ($webinars as $webinar) {
            dispatch(new RunAutoWebinar($webinar));
        }
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($exception);
        }
    }
}
