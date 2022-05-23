<?php

namespace App\Console\Commands;

use App\Services\UserService;
use Illuminate\Console\Command;

/**
 * Class SimulateUsersOnline
 * @package App\Console\Commands
 */
class MakeFakeUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:fakeusers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes fake users';
    /**
     * @var UserService
     */
    private $userService;

    /**
     * Create a new command instance.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $expectedUsersOnlineCount = $this->ask('How many users do you want to make?');
        $firstId = $this->ask('From what number of fake ID should we begin?');
        if (!$this->confirm('Use default password?', true)) {
            $password = $this->ask('Enter the password');
        } else {
            $password = 'qoi73dnD37';
        }

        $bar = $this->output->createProgressBar($expectedUsersOnlineCount);
        $bar->start();

        for ($i = $firstId; $i < $expectedUsersOnlineCount + $firstId; $i++) {
            $email = 'fake' . $i . '@edly.club';
            $user = $this->userService->createByEmail($email, $password);
            $user->email_verified_at = now();
            $user->save();

            $bar->advance();
        }

        $bar->finish();
    }
}
