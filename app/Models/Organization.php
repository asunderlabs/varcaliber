<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Gate;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'client_id',
        'stripe_customer_id',
        'billing_contact',
        'email',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip_code',
        'hourly_rate',
    ];

    public $appends = [
        'short_code',
        'can_view_stats'
    ];

    public function getShortCodeAttribute()
    {
        return strtoupper(str_split(str_replace(' ', '', $this->name), 8)[0]);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class)->with('items')->orderBy('starts_at', 'asc');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderBy('number', 'asc');
    }

    public function issuedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class)
            ->orderBy('number', 'asc')
            ->whereNotNull('delivered_at')
            ->where('issue_at', '<=', now());
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stripePaymentMethods(): HasMany
    {
        return $this->hasMany(StripePaymentMethod::class);
    }

    // Get unpaid invoices
    public function balance()
    {
        $balance = 0;

        $invoices = $this->issuedInvoices->where('paid', false);
        if ($invoices->count()) {
            foreach ($invoices as $invoice) {
                $balance += $invoice->total;
            }
        }

        return $balance;
    }

    public function billTotal()
    {
        $total = 0;

        // Get estimated total from bill
        $billingCycle = Report::billingCycle();

        $report = $this->reports
            ->where('starts_at', $billingCycle['start'])
            ->where('ends_at', $billingCycle['end'])
            ->sortBy('ends_at')
            ->reverse()
            ->first();

        if ($report) {
            foreach ($report->items as $item) {
                $total += round($item->minutes / 60 * $item->hourly_rate) * 100;
            }
        }

        return $total;
    }

    public function billableAmountCurrentWeek()
    {
        $total = 0;

        $billingCycle = Report::billingCycle();

        $report = $this->reports
            ->where('starts_at', $billingCycle['start'])
            ->where('ends_at', $billingCycle['end'])
            ->sortBy('ends_at')
            ->reverse()
            ->first();

        if (! $report) {
            return $total;
        }

        $items = $report->items->where('created_at', '>=', now()->startOfWeek())->where('created_at', '<=', now()->endOfWeek());

        if ($items->count()) {
            foreach ($items as $item) {
                $total += round($item->minutes / 60 * $item->hourly_rate) * 100;
            }
        }

        return $total;
    }

    public function hours()
    {
        $hours = 0;

        $billingCycle = Report::billingCycle();

        $report = Report::where('organization_id', $this->id)
            ->where('starts_at', $billingCycle['start'])
            ->where('ends_at', $billingCycle['end'])
            ->with('items')
            ->orderBy('ends_at', 'desc')
            ->first();

        if ($report) {
            foreach ($report->items as $item) {
                $hours += round($item->minutes / 60 * 100) / 100;
            }
        }

        return $hours;

    }

    public function nextInvoiceNumber()
    {
        $lastInvoice = $this->invoices->last();
        // $lastInvoice = Invoice::where('organization_id', $this->organization_id)
        //     ->orderBy('number', 'desc')
        //     ->first();

        return $lastInvoice ? $lastInvoice->number + 1 : 1;
    }

    public function getCanViewStatsAttribute()
    {
        return Gate::allows('viewStats', $this);
    }
}
