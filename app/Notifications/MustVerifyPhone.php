<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Class MustVerifyPhone
 * @package App\Notifications
 */
class MustVerifyPhone extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     */
    public function __construct()
    {
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
        return new BroadcastMessage(['phone' => $notifiable->phone]);
    }

    /**
     * Get the type of the notification being broadcast.
     *
     * @return string
     */
    public function broadcastType()
    {
        return 'MustVerifyPhone';
    }
}
