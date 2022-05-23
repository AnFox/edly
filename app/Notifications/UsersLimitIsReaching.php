<?php

namespace App\Notifications;

use App\Http\Resources\WebinarPublicResource;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Class UsersLimitIsReaching
 * @package App\Notifications
 */
class UsersLimitIsReaching extends BaseNotification
{
    use Queueable;

    private Webinar $webinar;

    public function __construct(Webinar $webinar)
    {
        $this->webinar = $webinar;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast'];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param User $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'webinar' => new WebinarPublicResource($this->webinar),
        ]);
    }

    public function toMail($notifiable)
    {

    }

    /**
     * Get the type of the notification being broadcast.
     *
     * @return string
     */
    public function broadcastType()
    {
        return 'UsersLimitIsReaching';
    }
}
