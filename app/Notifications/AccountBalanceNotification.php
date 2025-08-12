<?php

namespace App\Notifications;

use App\Models\Email;
use App\Models\Organization;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountBalanceNotification extends Notification
{
    use Queueable;

    private $organization;

    private $ccUsers;

    private $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
        $this->ccUsers = $organization->users->where('preferences.account_notification_email_cc', true);
        $this->url = route('billing.index');
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
        $balance = '$' . number_format($this->organization->billTotal() / 100, 2);
        $billingCycleEnd = Report::billingCycle()['end']->setTimezone('America/Chicago')->format('F j, Y');

        $email = Email::create([
            'type' => 'Account Balance',
            'subject' => 'Account Balance for ' . $this->organization->name,
            'to' => $notifiable->email,
            'cc' => $this->ccUsers->implode('email', ', '),
            'status' => 'created',
            'organization_id' => $this->organization->id,
        ]);

        return (new MailMessage)
            ->cc($this->ccUsers->pluck('email'))
            ->subject($email->subject)
            ->greeting('Hello ' . $notifiable->first_name . ',')
            ->line("Your current account balance is {$balance} for the billing period ending on {$billingCycleEnd}.")
            ->action('View Account', $this->url)
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
