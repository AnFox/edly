<?php

namespace App\Providers;

use App\Events\NewRecordableAction;
use App\Listeners\RecordScriptCommand;
use App\Listeners\SynchronizeUser;
use Illion\UserSync\Events\UserSyncDataReceived;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
//            SendPhoneVerificationNotification::class,
//            SendEmailVerificationNotification::class,
        ],
        UserSyncDataReceived::class => [
            SynchronizeUser::class,
        ],
        NewRecordableAction::class => [
            RecordScriptCommand::class
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
