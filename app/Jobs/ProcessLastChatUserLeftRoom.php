<?php

namespace App\Jobs;

use App\Contracts\Repositories\ChatRepository;
use App\Contracts\Repositories\WebinarRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessLastChatUserLeftRoom implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var int
     */
    private $chatId;

    /**
     * Create a new job instance.
     *
     * @param int $chatId
     */
    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var ChatRepository $chatRepository */
        $chatRepository = app(ChatRepository::class);

        /** @var WebinarRepository $webinarRepository */
        $webinarRepository = app(WebinarRepository::class);

        $webinar = $chatRepository->find($this->chatId)->getWebinar();
        \Log::debug(__CLASS__, [
            'chat ID' => $this->chatId,
            'webinar ID' => $webinar->id
        ]);

        $webinarRepository->setModel($webinar);
        $webinarRepository->setAllVisitorsOffline();
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
