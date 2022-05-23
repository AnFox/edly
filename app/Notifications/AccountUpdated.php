<?php

namespace App\Notifications;

use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class AccountUpdated
 * @package App\Notifications
 */
class AccountUpdated extends BaseNotification
{
    use Queueable;

    /**
     * @var Account
     */
    private $account;

    /**
     * PaymentSucceed constructor.
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
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
            'account' => new AccountResource($this->account),
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
        return 'AccountUpdated';
    }
}
