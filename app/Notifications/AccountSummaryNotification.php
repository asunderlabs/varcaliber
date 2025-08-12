<?php

namespace App\Notifications;

use App\Mail\AccountSummaryMail;
use App\Models\Email;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountSummaryNotification extends Notification
{
    use Queueable;

    private $organization;

    private $ccUsers;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
        $this->ccUsers = $organization->users->where('preferences.account_notification_email_cc', true);
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
            'type' => 'Account Summary',
            'subject' => 'Account Summary for ' . $this->organization->name,
            'to' => $notifiable->email,
            'cc' => $this->ccUsers->implode('email', ', '),
            'status' => 'created',
            'organization_id' => $this->organization->id,
        ]);

        return (new AccountSummaryMail($this->organization, $notifiable))
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
