<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if (Gate::denies('viewAny', Payment::class) && Gate::denies('viewAnyInOrganization', Payment::class)) {
            abort(403);
        }

        $organization = OrganizationService::getOrganization($request);

        return Inertia::render('Payments', [
            'organization' => $organization,
            'payments' => Payment::where('organization_id', $organization->id)
                ->with('invoices')
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ]);
    }

    public function show(Payment $payment)
    {
        Gate::authorize('view', $payment);

        $payment->load('invoices', 'organization');

        return Inertia::render('Admin/Payment', [
            'payment' => $payment,
        ]);
    }

    public function create(Invoice $invoice)
    {
        Gate::authorize('create', Payment::class);

        return Inertia::render('Admin/AddPayment', [
            'invoice' => $invoice,
            'payments' => array_values($invoice->organization->payments->reverse()->toArray()),
        ]);
    }

    public function store()
    {
        Gate::authorize('create', Payment::class);

        $payment = Payment::create([
            'paid_at' => request()->paid_at,
            'amount' => request()->amount,
            'organization_id' => request()->organization_id,
        ]);

        $affectedInvoices = [];

        if (request()->invoices) {
            foreach (request()->invoices as $invoiceData) {
                $invoice = Invoice::find($invoiceData['id']);
                $invoice->paid = $invoiceData['paid'];
                $invoice->save();
                $payment->invoices()->attach($invoice->id);
                $payment->invoices()->updateExistingPivot($invoice->id, ['amount' => request()->amount_applied]);
                $affectedInvoices[] = $invoice;
            }
        }

        if (count($affectedInvoices) === 1 && $invoice->paid) {
            // Payment details page with button to send notification
            return redirect()->route('admin.payments.show', $payment->id)->with('message', 'Payment created and added to invoice #' . $invoice->invoice_number);
        }

        $affectedInvoiceNumbers = array_map(function ($item) {
            return "Invoice #{$item->invoice_number}";
        }, $affectedInvoices);

        return redirect()->route('admin.invoices.index')->with('message', 'Payment created and added to ', implode(', ', $affectedInvoiceNumbers));
    }

    public function notify(Payment $payment)
    {
        Gate::authorize('update', Payment::class);

        $payment->notifyUser();

        return redirect()->route('admin.invoices.index')->with('message', 'Payment notification sent!');
    }

    // Attach payment to invoice
    public function attach(Payment $payment, Invoice $invoice)
    {
        Gate::authorize('update', Payment::class);

        $payment->invoices()->attach($invoice->id);
        $payment->invoices()->updateExistingPivot($invoice->id, ['amount' => request()->amount_applied]);
        $invoice->paid = request()->paid;
        $invoice->save();

        return redirect()->route('admin.invoices.index')->with('message', 'Payment added to invoice #' . $invoice->invoice_number);
    }
}
