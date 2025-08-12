<?php

namespace App\Notifications;

use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicesCreatedNotification extends Notification
{
    use Queueable;

    private $count;

    private $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($count)
    {
        $this->count = $count;
        $this->url = route('admin.invoices.index');
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
            'type' => 'Invoices Created',
            'subject' => "{$this->count} New Invoice" . ($this->count > 1 ? 's' : '') . ' Awaiting Approval',
            'to' => $notifiable->email,
            'status' => 'created',
        ]);

        return (new MailMessage)
            ->subject($email->subject)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line('You have one or more new invoices awaiting approval.')
            ->action('View Invoices', $this->url)
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
