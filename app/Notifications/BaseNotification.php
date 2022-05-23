<?php


namespace App\Notifications;


use Illuminate\Notifications\Notification;

/**
 * Class BaseNotification
 * @package App\Notifications
 */
class BaseNotification extends Notification
{

    /**
     * The notification failed to process.
     *
     * @param $exception
     * @return void
     */
    public function failed($exception)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureException($exception);
        }
    }
}
