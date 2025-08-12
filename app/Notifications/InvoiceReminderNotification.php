<?php

namespace App\Notifications;

use App\Mail\InvoiceReminderMail;
use App\Models\Email;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceReminderNotification extends Notification
{
    use Queueable;

    private $invoice;

    private $ccUsers;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->ccUsers = $invoice->organization->users->where('preferences.invoice_notification_email_cc', true);
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
     */
    public function toMail($notifiable)
    {
        $email = Email::create([
            'type' => 'Invoice Reminder',
            'subject' => 'Reminder: Invoice Payment Due for ' . $this->invoice->organization->name,
            'to' => $notifiable->email,
            'cc' => $this->ccUsers->implode('email', ', '),
            'status' => 'created',
            'organization_id' => $this->invoice->organization_id,
        ]);

        return (new InvoiceReminderMail($this->invoice, $notifiable))
            ->to($notifiable->email)
            ->cc($this->ccUsers->pluck('email'))
            ->subject($email->subject)
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
