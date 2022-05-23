<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\WebinarRepository;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunAutoWebinars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webinar:run-auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs auto webinars';
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
     * @return int
     */
    public function handle()
    {
        $processes = [];

        $webinars = $this->webinarRepository->getCurrentAutoWebinars();
        foreach ($webinars as $webinar) {
            $webinar->is_playing = true;
            $webinar->save();

            $process = Process::fromShellCommandline('php ' . base_path('artisan') . " webinar:play-auto {$webinar->id}", base_path());
            $process->setTimeout(0);
            $process->disableOutput();
            $process->start();
            $processes[] = $process;
        }

        while (count($processes)) {
            foreach ($processes as $i => $runningProcess) {
                if (! $runningProcess->isRunning()) {
                    unset($processes[$i]);
                }
                sleep(1);
            }
        }

        return 0;
    }
}
