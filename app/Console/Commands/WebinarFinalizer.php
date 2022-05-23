<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\WebinarRepository;
use Illuminate\Console\Command;

/**
 * Class WebinarFinalizer
 * @package App\Console\Commands
 */
class WebinarFinalizer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webinar:finalize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finishes webinars that has past due finish time.';

    /**
     * @var WebinarRepository
     */
    private $webinarRepository;

    /**
     * Create a new command instance.
     *
     * @param WebinarRepository $webinarRepository
     */
    public function __construct(WebinarRepository $webinarRepository)
    {
        parent::__construct();
        $this->webinarRepository = $webinarRepository;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $webinars = $this->webinarRepository->unfinished();

        foreach ($webinars as $webinar) {
            $this->webinarRepository->setModel($webinar);
            if ($this->webinarRepository->isStarted() && $this->webinarRepository->isFinishTimeWithPastDue()) {
                $this->webinarRepository->setFinished();
                $this->webinarRepository->setAllVisitorsOffline();
            }
        }
    }
}
