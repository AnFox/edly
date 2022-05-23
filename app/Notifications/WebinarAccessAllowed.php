<?php

namespace App\Notifications;

use App\Models\Webinar;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class WebinarAccessAllowed
 * @package App\Notifications
 */
class WebinarAccessAllowed extends BaseNotification
{
    use Queueable;
    /**
     * @var Webinar
     */
    private $webinar;


    /**
     * Create a new notification instance.
     *
     * @param Webinar $webinar
     */
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
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Вам открыт доступ к вебинару ' . $this->webinar->name)
            ->line('Поздравляем, Вам открыт доступ к вебинару ' . $this->webinar->name);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        //
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'webinar_id' => $this->webinar->id,
            'access_allowed' => true,
        ]);
    }

    /**
     * Get the type of the notification being broadcast.
     *
     * @return string
     */
    public function broadcastType()
    {
        return 'WebinarAccessAllowed';
    }
}
