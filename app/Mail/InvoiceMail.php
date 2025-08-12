<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
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
        $this->invoice = $invoice;
        $this->notifiable = $notifiable;
        $this->greeting = 'Hello ' . $this->notifiable->first_name . ',';
        $this->introLines = [
            'Please find your invoice attached. You may also click the link below to view the invoice on the billing website.',
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
