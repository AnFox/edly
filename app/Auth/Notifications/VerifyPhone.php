<?php

namespace Modules\Token\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Nutnet\LaravelSms\Notifications\NutnetSmsChannel;
use Nutnet\LaravelSms\Notifications\NutnetSmsMessage;

/**
 * Class VerifyPhone
 * @package Modules\Token\Notifications
 */
class VerifyPhone extends Notification implements ShouldQueue
{
    use Queueable;

    protected $token;
    protected $serviceName;
    protected $password;

    /**
     * VerifyPhone constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->token = $params['code'];
    }

    public function via($notifiable)
    {
        return [NutnetSmsChannel::class];
    }

    /**
     * @param $notifiable
     * @return NutnetSmsMessage
     */
    public function toNutnetSms($notifiable)
    {
        Log::channel('sentry')->info('Phone verification code ' . $this->token . ' for user ' . auth()->user()->email);

        return new NutnetSmsMessage('Код подтверждения: ' . $this->token);
    }
}
