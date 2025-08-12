<?php

namespace App\Notifications;

use App\Models\Email;
use Illuminate\Notifications\Messages\MailMessage;
use Spatie\WelcomeNotification\WelcomeNotification;

class CustomWelcomeNotification extends WelcomeNotification
{
    private $notifiable;

    public function toMail($notifiable)
    {
        $this->initializeNotificationProperties($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable);
        }

        $this->notifiable = $notifiable;

        return $this->buildWelcomeNotificationMessage();
    }

    public function buildWelcomeNotificationMessage(): MailMessage
    {
        $email = Email::create([
            'type' => 'Welcome',
            'subject' => 'Varcaliber Invitation',
            'to' => $this->notifiable->email,
            'status' => 'created',
        ]);

        return (new MailMessage)
            ->subject($email->subject)
            ->greeting('Hello ' . $this->notifiable->first_name . ',')
            ->line('Please use the link below to create a password and log into your account. Your username is your email address ("' . $this->notifiable->email . '").')
            ->action('Create Password', $this->showWelcomeFormUrl)
            ->metadata('email_id', (string) $email->id);
    }
}
