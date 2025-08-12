<?php

namespace App\Mail;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    private $notifiable;

    public $invoice;

    public $greeting;

    public $level;

    public $actionText;

    public $actionUrl;

    public $displayableActionUrl;

    public $introLines = [];

    public $outroLines = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, $notifiable)
    {
        $dateDue = (new Carbon($invoice->due_at))->setTimezone('America/Chicago');
        $isPastDue = false;

        if (now('America/Chicago')->toDateString() === $dateDue->toDateString()) {
            $isDueText = 'is due today';
        } elseif ($dateDue < now('America/Chicago')) {
            $isDueText = 'is past due';
            $isPastDue = true;
        } else {
            $isDueText = 'is due on ' . $dateDue->format('F j, Y');
        }

        $invoiceNumber = str_pad((string) $invoice->number, 4, '0', STR_PAD_LEFT);

        $this->invoice = $invoice;
        $this->notifiable = $notifiable;
        $this->greeting = 'Hello ' . $this->notifiable->first_name . ',';
        $this->introLines = [
            "Please be aware that your payment for Invoice #{$invoiceNumber} {$isDueText}. If you have already submitted your payment, please " . ($isPastDue ? 'let me know' : 'ignore this message') . '.',
        ];
        $this->actionText = 'View Invoice';
        $this->actionUrl = route('invoices.show', $invoice->id);
        $this->displayableActionUrl = $this->actionUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('app.company'))
            ->attachFromStorage($this->invoice->filepath, $this->invoice->filename, [
                'mime' => 'application/pdf',
            ])
            ->markdown('email.email');
    }
}
