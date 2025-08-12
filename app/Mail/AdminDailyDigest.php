<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminDailyDigest extends Mailable
{
    use Queueable, SerializesModels;

    public $stats;

    public $errors;

    public $scheduleFailures;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($stats, $errors, $scheduleFailures)
    {
        $this->stats = $stats;
        $this->errors = $errors;
        $this->scheduleFailures = $scheduleFailures;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('app.name'))
            ->markdown('email.adminDailyDigestEmail');
    }
}
