<?php

namespace App\Http\Controllers;

use App\Mail\AccountSummaryMail;
use App\Models\Invoice;
use App\Models\Organization;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

// use Barryvdh\DomPDF\Facade\Pdf;

class DevController extends Controller
{
    // Generates invoice PDF
    public function invoice(Request $request, Invoice $invoice)
    {
        $logoBase64 = Storage::exists('public/'.config('settings.company_logo_path')) ? base64_encode(storage::get('public/'.config('settings.company_logo_path'))) : null;
        $company = [
            'name' => config('settings.company_name'),
            'address' => config('settings.company_address'),
            'phone' => config('settings.company_phone'),
            'email' => config('settings.company_email'),
        ];
        $invoice->pay_by_bank_discount = 3500;
        $invoice->note = 'This is an example note.';
        $url = URL::signedRoute('dev.payInvoice', ['invoice' => $invoice->id]);

        $pdf = PDF::loadView('pdf/invoice', compact('logoBase64', 'company', 'invoice', 'url'));

        // View in browser
        return $pdf->stream();

        // Save file
        // return $pdf->save(storage_path('app/private') . "/invoices/-{$invoice->invoice_number}.pdf");

        // Download
        // return $pdf->download('invoice.pdf');
    }

    public function payInvoice(Request $request, Invoice $invoice)
    {
        $invoice->pay_by_bank_discount = 3500;

        return Inertia::render('PayInvoice2', [
            'invoice' => $invoice->load(['organization', 'organization.stripePaymentMethods']),
            'paymentMethods' => [
                [
                    'id' => 'bank_account',
                    'name' => 'Bank Account',
                    'fee_percent' => 0,
                    'discount' => $invoice->pay_by_bank_discount,
                ],
                [
                    'id' => 'credit_card',
                    'name' => 'Credit Card',
                    'fee_percent' => 0.02,
                    'discount' => null,
                ],
            ],
        ]);
    }

    public function accountSummaryEmail(?Organization $organization = null)
    {
        $organization = $organization ?? Organization::where('name', 'Test Organization')->firstOrFail();
        $user = $organization->users->first();
        return new AccountSummaryMail($organization, $user);
    }
}
