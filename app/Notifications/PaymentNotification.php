<?php

namespace App\Notifications;

use App\Models\Email;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification
{
    use Queueable;

    private $payment;

    private $ccUsers;

    private $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->ccUsers = $payment->organization->users->where('preferences.invoice_notification_email_cc', true);
        $this->url = route('payments');
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
        $email = Email::create([
            'type' => 'Payment',
            'subject' => 'Payment for ' . $this->payment->organization->name,
            'to' => $notifiable->email,
            'cc' => $this->ccUsers->implode('email', ', '),
            'status' => 'created',
            'organization_id' => $this->payment->organization_id,
        ]);

        $amount = number_format($this->payment->amount / 100, 2);

        return (new MailMessage)
            ->cc($this->ccUsers->pluck('email'))
            ->subject($email->subject)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('A new payment of $' . $amount . " has been received for {$this->payment->organization->name}.")
            ->action('View Payments', $this->url)
            ->metadata('email_id', (string) $email->id);
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
