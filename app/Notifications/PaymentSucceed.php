<?php

namespace App\Notifications;

use App\Http\Resources\PaymentResource;
use App\Http\Resources\UserResource;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class PaymentSucceed
 * @package App\Notifications
 */
class PaymentSucceed extends BaseNotification
{
    use Queueable;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * PaymentSucceed constructor.
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast', 'mail'];
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
            'payment' => new PaymentResource($this->payment),
            'user' => new UserResource($this->payment->order->user->fresh()),
        ]);
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Успешная оплата')
            ->view('email.payment_success', ['id' => $this->payment->id, 'amount' => $this->payment->amount]);
    }

    /**
     * Get the type of the notification being broadcast.
     *
     * @return string
     */
    public function broadcastType()
    {
        return 'PaymentSucceed';
    }
}
