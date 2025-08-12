<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentIntentNotification extends Notification
{
    use Queueable;

    private $eventType;
    private $amount;
    private $paymentEmail;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($type, $paymentIntent)
    {   
        $this->eventType = $type;
        $this->amount = $paymentIntent->amount;
        $this->paymentEmail = $paymentIntent->receipt_email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Stripe Payment')
                    ->line('Event Type: ' . $this->eventType)
                    ->line('Amount: $' . number_format($this->amount / 100, 2))
                    ->line('Email: ' . $this->paymentEmail);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
