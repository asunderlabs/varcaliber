<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    use HasFactory;

    protected $with = ['payments', 'organization'];

    protected $fillable = [
        'number',
        'billing_start',
        'billing_end',
        'issue_at',
        'delivered_at',
        'approved_at',
        'due_at',
        'paid',
        'payment_is_pending',
        'client_info',
        'items',
        'subtotal',
        'tax',
        'processing_fee',
        'amount_discounted',
        'total',
        'note',
        'pay_by_bank_discount',
        'organization_id',
        'manual_delivery',
    ];

    public $appends = [
        'invoice_number',
        'filename',
        'filepath',
        'amount_due',
        'local_date',
    ];

    protected $casts = [
        'client_info' => 'array',
        'items' => 'array',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getInvoiceNumberAttribute()
    {
        return $this->organization->client_id . str_pad((string) $this->number, 4, '0', STR_PAD_LEFT);
    }

    public function getFilenameAttribute()
    {
        return "Invoice-{$this->invoice_number}.pdf";
    }

    public function getFilepathAttribute()
    {
        $filename = "invoice-{$this->invoice_number}.pdf";

        return config('app.debug') ? "/private/{$filename}" : "/invoices/{$filename}";
    }

    public function getAmountDueAttribute()
    {
        if (! $this->payments->count()) {
            return $this->total;
        }

        $amountDue = $this->total;

        foreach ($this->payments as $payment) {
            $amountDue -= $payment->getRelationValue('pivot')->amount;
        }

        return $amountDue;
    }

    public function getLocalDateAttribute()
    {
        return (new Carbon($this->issue_at))->setTimezone('America/Chicago')->toDateString();
    }

    public function notifyUser()
    {
        $user = $this->organization->users->where('preferences.invoice_notification_email', true)->first();
        $user->sendInvoiceNotification($this);
    }

    public function createPDF()
    {
        $this->load('organization');
        $pdf = PDF::loadView('pdf/invoice', [
            'logoBase64' => Storage::exists('public/'.config('settings.company_logo_path')) ? base64_encode(storage::get('public/'.config('settings.company_logo_path'))) : null,
            'company' => [
                'name' => config('settings.company_name'),
                'address' => config('settings.company_address'),
                'phone' => config('settings.company_phone'),
                'email' => config('settings.company_email'),
            ],
            'invoice' => $this,
            'url' => $this->organization->stripe_customer_id ? route('invoices.pay', ['invoice' => $this->id]) : null,
        ]);

        Storage::disk()
            ->put(
                $this->filepath,
                $pdf->output()
            );
    }

    public function getPDF()
    {
        return Storage::get($this->filepath);
    }

    public function downloadPDF()
    {
        return Storage::download($this->filepath, $this->filename);
    }

    public function deletePDF()
    {
        return Storage::delete($this->filepath);
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class)->withPivot('amount');
    }
}
