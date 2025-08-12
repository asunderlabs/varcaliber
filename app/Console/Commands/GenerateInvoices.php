<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:generate {reportId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate invoices using reports from current billing period';

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
     * 
     * This will generate an invoices for any billable_hours report that ends today,
     * unless you provide a report ID.
     * 
     * Running this multiple times may generate duplicate invoices
     */
    public function handle(): void
    {
        $reportId = $this->argument('reportId');

        if ($reportId) {
            $report = Report::findOrFail($reportId);
            $reports = [$report];
        } else {
            $reports = Report::where('ends_at', now()->endOfDay())
                ->where('report_type', 'billable_hours')
                ->with(['organization', 'items'])
                ->get();
        }

        $invoiceCount = 0;

        // TODO: Separate this logic into an Invoice Service
        foreach ($reports as $report) {

            $organization = $report->organization;

            $items = $report->billItems();

            $subtotal = 0;

            foreach ($items as &$item) {
                $item['hours'] = round($item['minutes'] / 60 * 100) / 100;
                $item['amount'] = $item['hours'] * $item['hourly_rate'] * 100;
                $subtotal += $item['amount'];
                unset($item['minutes']);
            }

            $issueAt = (new Carbon($report->ends_at))->addDay()->setTimezone('America/Chicago');
            $issueAt = (new Carbon($issueAt->toDateString() . ' ' . config('app.issue_invoice_at_time'), 'America/Chicago'))->setTimezone('UTC');

            $dueAt = (new Carbon($report->ends_at))->addDays(config('app.invoice_due_after_days'))->setTimezone('America/Chicago');
            $dueAt = (new Carbon($dueAt->toDateString() . ' ' . config('app.invoice_due_at_time'), 'America/Chicago'))->setTimezone('UTC');

            $invoice = $organization->invoices()->save(new Invoice([
                'number' => $organization->nextInvoiceNumber(),
                'billing_start' => $report->starts_at,
                'billing_end' => $report->ends_at,
                'issue_at' => $issueAt,
                'due_at' => $dueAt,
                'client_info' => [
                    $organization->billing_contact,
                    $organization->name,
                    "{$organization->address_line_1} {$organization->address_line_2}",
                    "{$organization->city} {$organization->state} {$organization->zip_code}",
                ],
                'items' => $items,
                'subtotal' => $subtotal,
                'tax' => 0,
                'total' => $subtotal,
            ]));

            $invoice->createPDF();

            $invoiceCount++;

            echo "Created invoice #{$invoice->invoice_number} for {$organization->name} \n";
        }

        if ($invoiceCount > 0) {
            $admin = User::where('is_admin', true)->first();
            $admin->sendInvoicesCreatedNotification($invoiceCount);
        }

        echo "Done - {$invoiceCount} invoices created. \n";

    }
}
