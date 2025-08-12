<?php

namespace App\Mail;

use App\Models\Organization;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    private $notifiable;

    public $organization;

    public $greeting;

    public $level;

    public $actionText;

    public $actionUrl;

    public $displayableActionUrl;

    public $introLines = [];

    public $outroLines = [];

    public $start;

    public $end;

    public $weeks = [];

    public $billableHoursReport;

    public $billableHoursAmountTotal;

    public $invoiceRows = [];

    public $invoiceTotal = 0;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Organization $organization, $notifiable)
    {
        $this->organization = $organization;
        $this->notifiable = $notifiable;
        $this->greeting = 'Hi ' . $this->notifiable->first_name . ',';
        $this->introLines = [
            'Please review your account summary below.',
        ];

        $this->billableHoursSection();
        $this->invoicesSection();
        
        $this->actionText = 'View Report';
        $this->actionUrl = route('reports.index');
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
            ->markdown('email.accountSummary');
    }

    private function billableHoursSection()
    {
        $this->billableHoursReport = Report::where('organization_id', $this->organization->id)
            ->where('report_type', 'billable_hours')
            ->where('ends_at', '>=', now())
            ->first();
        $this->start = (new Carbon($this->billableHoursReport->starts_at))->setTimezone('America/Chicago')->format('M j, Y');
        $this->end = (new Carbon($this->billableHoursReport->ends_at))->setTimezone('America/Chicago')->format('M j, Y');

        $billableHoursAmountTotal = $this->getReportAmountTotal($this->billableHoursReport);
        $billableHoursAmountThisWeek = $this->getReportAmountThisWeek($this->billableHoursReport);
        $billableHoursAmountLastWeek = $billableHoursAmountTotal - $billableHoursAmountThisWeek;

        $this->weeks = [
            [
                'label' => 'This week',
                'amount' => $this->formatDollars($billableHoursAmountThisWeek),
            ],
        ];

        if ($billableHoursAmountLastWeek) {
            $this->weeks[] = [
                'label' => 'Last week',
                'amount' => $this->formatDollars($billableHoursAmountLastWeek),
            ];
        }

        $this->billableHoursAmountTotal = $this->formatDollars($billableHoursAmountTotal);
    }

    private function invoicesSection()
    {
        foreach ($this->organization->issuedInvoices->where('paid', false) as $invoice) {
            $payments = $invoice->payments->reduce(function ($carry, $payment) {
                return $carry + $payment->getRelationValue('pivot')->amount;
            });
            $remainder = $invoice->total - $payments;
            $this->invoiceTotal += $remainder;
            $this->invoiceRows[] = [
                'label' => "Invoice #{$invoice->number} " . ($payments ? '(partially paid)' : ''),
                'amount' => $this->formatDollars($remainder),
            ];
        }

        $this->invoiceTotal = $this->formatDollars($this->invoiceTotal);
    }

    private function formatDollars($amount)
    {
        return '$' . number_format($amount / 100, 2);
    }

    private function getReportAmountTotal(Report $report)
    {
        $total = 0;   

        if ($report) {
            foreach ($report->items as $item) {
                $total += round($item->minutes / 60 * $item->hourly_rate) * 100;
            }
        }

        return $total;
    }

    private function getReportAmountThisWeek(Report $report)
    {
        $total = 0;

        if (! $report) {
            return $total;
        }

        $report->load('items.workEntry');

        $items = $report->items->where('workEntry.starts_at', '>=', now()->startOfWeek())->where('workEntry.starts_at', '<=', now()->endOfWeek());

        if ($items->count()) {
            foreach ($items as $item) {
                $total += round($item->minutes / 60 * $item->hourly_rate) * 100;
            }
        }

        return $total;
    }
}
