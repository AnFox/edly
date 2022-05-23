<?php

namespace App\Jobs;

use App\Models\Webinar;
use App\Services\ScriptService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

/**
 * Class RunAutoWebinar
 * @package App\Jobs
 */
class RunAutoWebinar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;
    /**
     * @var Webinar
     */
    private $webinar;

    /**
     * Create a new job instance.
     *
     * @param Webinar $webinar
     */
    public function __construct(Webinar $webinar)
    {
        $this->webinar = $webinar;
    }

    /**
     * Execute the job.
     *
     * @param ScriptService $scriptService
     * @return void
     */
    public function handle(ScriptService $scriptService)
    {
        set_time_limit(0);
        try {
            $scriptService->play($this->webinar);
        } catch (\Exception $e) {
            Log::error('Failed to play script for auto webinar ID: ' . $this->webinar->id, [$e]);
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
