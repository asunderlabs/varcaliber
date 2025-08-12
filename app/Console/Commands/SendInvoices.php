<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class SendInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send invoices';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Find any undelivered invoices that are approved for delivery and have an issue at date of today
        $invoices = Invoice::whereNull('delivered_at')
            ->whereNotNull('approved_at')
            ->where('manual_delivery', false)
            ->get();

        $deliveries = 0;

        $tomorrow = now('America/Chicago')->addDay()->startOfDay()->setTimezone('UTC');

        foreach ($invoices as $invoice) {
            // Allow sending invoice from the issue at time until the end of the day. Don't send if it's not the same date
            if (now() >= $invoice->issue_at && $invoice->issue_at < $tomorrow) {
                $invoice->notifyUser();
                $invoice->delivered_at = now()->toDateTimeString();
                $invoice->save();

                echo "Sent invoice #{$invoice->invoice_number}\n";
                info("Sent invoice #{$invoice->invoice_number}");

                $deliveries++;
            }
            // TODO: Notifiy self if there are invoices that haven't been delivered and their issue at date has passed.
        }

        echo "Invoices sent: {$deliveries}\n";
    }
}
