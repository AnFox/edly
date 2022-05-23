<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\WebinarRepository;
use App\Services\ScriptService;
use Illuminate\Console\Command;

class PlayAutoWebinar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webinar:play-auto {webinarId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Plays auto webinar';
    /**
     * @var WebinarRepository
     */
    private $webinarRepository;
    /**
     * @var ScriptService
     */
    private $scriptService;

    /**
     * Create a new command instance.
     *
     * @param WebinarRepository $webinarRepository
     * @param ScriptService $scriptService
     */
    public function __construct(WebinarRepository $webinarRepository, ScriptService $scriptService)
    {
        parent::__construct();
        $this->webinarRepository = $webinarRepository;
        $this->scriptService = $scriptService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $webinarId = $this->argument('webinarId');
        $webinar = $this->webinarRepository->find($webinarId)->getModel();
        \Log::debug('PlayAutoWebinar: ' . $webinarId);

        try {
            $this->scriptService->play($webinar);
            $webinar->is_playing = false;
            $webinar->save();
        } catch (\Exception $e) {
            \Log::error('Failed to play script for auto webinar ID: ' . $webinar->id, [$e]);
        }

        return 0;
    }
}
