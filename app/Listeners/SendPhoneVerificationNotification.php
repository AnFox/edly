<?php

namespace Illuminate\Auth\Listeners;

use App\Contracts\Auth\Middleware\MustVerifyPhone;
use Illuminate\Auth\Events\Registered;

/**
 * Class SendPhoneVerificationNotification
 * @package Illuminate\Auth\Listeners
 */
class SendPhoneVerificationNotification
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if ($event->user instanceof MustVerifyPhone && ! $event->user->hasVerifiedPhone()) {
            $event->user->sendPhoneVerificationNotification();
        }
    }
}
