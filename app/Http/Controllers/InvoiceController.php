<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Report;
use App\Services\OrganizationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Invoice::class);

        $organization = OrganizationService::getOrganization($request);

        return Inertia::render('Admin/Invoices', [
            'organization' => $organization,
            'invoices' => Invoice::with('organization')
                ->when($organization, function ($query, $organization) {
                    $query->where('organization_id', $organization->id);
                })
                ->orderBy('issue_at', 'desc')
                ->paginate(10),
        ]);
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);
        
        $invoice->load(['organization.invoices']);

        return Inertia::render('ShowInvoice', [
            'organization' => OrganizationService::getOrganization(request()),
            'invoice' => $invoice,
            'invoices' => $invoice->organization->invoices->sortBy('number')->reverse()->map(fn ($i) => [
                'id' => $i->id,
                'number' => $i->number,
                'issue_at' => $i->issue_at,
            ])->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Gate::authorize('create', Invoice::class);

        $billingCycle = Report::billingCycle();

        return Inertia::render('Invoices/Create', [
            'organizations' => Organization::all(),
            'billingStart' => $billingCycle['start'],
            'billingEnd' => $billingCycle['end'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        Gate::authorize('create', Invoice::class);

        $validated = request()->validate([
            'organization_id' => 'required|integer',
            'billingStart' => 'required',
            'billingEnd' => 'required',
            'issueAt' => 'required',
            'dueAt' => 'required',
            'delivery' => 'required',
        ]);

        $organization = Organization::findOrFail($validated['organization_id']);

        $start = (new Carbon($validated['billingStart'], 'America/Chicago'))->setTimezone('UTC');
        $end = (new Carbon($validated['billingEnd'], 'America/Chicago'))->addDay()->subMinute()->setTimezone('UTC');

        $issueAt = (new Carbon($validated['issueAt'], 'America/Chicago'));
        $issueAt = (new Carbon($issueAt->toDateString() . ' ' . config('app.issue_invoice_at_time'), 'America/Chicago'))->setTimezone('UTC');

        $dueAt = (new Carbon($validated['dueAt'], 'America/Chicago'));
        $dueAt = (new Carbon($dueAt->toDateString() . ' ' . config('app.invoice_due_at_time'), 'America/Chicago'))->setTimezone('UTC');

        $manualDelivery = $validated['delivery'] === 'manual';

        $invoice = Invoice::create([
            'number' => $organization->nextInvoiceNumber(),
            'billing_start' => $start->toDateTimeString(),
            'billing_end' => $end->toDateTimeString(),
            'issue_at' => $issueAt,
            'delivered_at' => $manualDelivery ? now() : null,
            'due_at' => $dueAt,
            'organization_id' => $organization->id,
            'client_info' => [
                $organization->billing_contact,
                $organization->name,
                "{$organization->address_line_1} {$organization->address_line_2}",
                "{$organization->city} {$organization->state} {$organization->zip_code}",
            ],
            'items' => [],
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'manual_delivery' => $manualDelivery,
        ]);

        $invoice->createPDF();

        return redirect()->route('admin.invoices.edit', $invoice->id)->with('message', 'Invoice created.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        return Inertia::render('Admin/EditInvoice', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        $validated = request()->validate([
            'number' => 'required|integer',
            'issueAtDate' => 'required',
            'issueAtTime' => 'required',
            'dueAtDate' => 'required',
            'dueAtTime' => 'required',
            'note' => 'nullable|max:255',
            'pay_by_bank_discount' => 'nullable|integer',
        ]);

        if (request()->pay_by_bank_discount >= request()->total) {
            return back()->with('error', 'Discount must be less than invoice total');
        }

        $invoice->number = intval(request()->number);
        $invoice->issue_at = (new Carbon(request()->issueAtDate . ' ' . request()->issueAtTime, 'America/Chicago'))->setTimezone('UTC');
        $invoice->delivered_at = $invoice->manual_delivery ? now() : $invoice->delivered_at;
        $invoice->due_at = (new Carbon(request()->dueAtDate . ' ' . request()->dueAtTime, 'America/Chicago'))->setTimezone('UTC');
        $invoice->items = request()->items;
        $invoice->subtotal = request()->subtotal;
        $invoice->tax = request()->tax;
        $invoice->total = request()->total;
        $invoice->note = request()->note;
        $invoice->pay_by_bank_discount = request()->pay_by_bank_discount;
        $invoice->save();

        $invoice->createPDF();

        return redirect()->route('admin.invoices.index')->with('message', "Invoice #{$invoice->invoice_number} was updated.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        Gate::authorize('delete', $invoice);

        $invoice->deletePDF();
        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('message', 'Invoice deleted.');
    }

    public function approve(Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        if (now() > $invoice->issue_at) {
            return redirect()->route('admin.invoices.index')->with('error', 'Cannot approve an invoice with an expired issue date. Please edit the invoice first.');
        }

        $invoice->approved_at = now();
        $invoice->save();

        return redirect()->route('admin.invoices.index')->with('message', "Invoice #{$invoice->invoice_number} was approved.");
    }

    public function cancelApproval(Invoice $invoice)
    {
        Gate::authorize('update', $invoice);

        $invoice->approved_at = null;
        $invoice->save();

        return redirect()->route('admin.invoices.index')->with('message', "Delivery for invoice #{$invoice->invoice_number} was canceled.");
    }

    public function preview(Invoice $invoice, string $filename)
    {
        Gate::authorize('view', $invoice);

        if ($filename !== $invoice->filename) {
            abort(404);
        }

        return response($invoice->getPDF(), 200)->withHeaders([
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename={$invoice->filename}",
        ]);
    }

    public function download(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        return $invoice->downloadPDF();
    }

    public function pay(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);
        
        if ($invoice->payments->count()) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Unable to pay invoice because there is already a payment.');
        }

        $organization = $invoice->organization;
        $organization->load('stripePaymentMethods');

        return Inertia::render('PayInvoice', [
            'organization' => $organization,
            'invoice' => $invoice,
        ]);
    }
}
