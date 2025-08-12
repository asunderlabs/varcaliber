<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Report;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function index(Request $request, string $localDate = null)
    {
        Gate::authorize('viewAnyInOrganization', Invoice::class);

        $organization = OrganizationService::getOrganization($request);

        $billingCycle = Report::billingCycle();

        $report = Report::where('organization_id', $organization->id)
            ->where('starts_at', $billingCycle['start'])
            ->where('ends_at', $billingCycle['end'])
            ->with('items')
            ->orderBy('ends_at', 'desc')
            ->first();

        $items = $report ? $report->billItems() : [];

        $invoices = $organization->issuedInvoices->where('paid', false);

        return Inertia::render('BillingSummary', [
            'organization' => $organization,
            'billingCycle' => $billingCycle ?? null,
            'report' => $report ?? null,
            'items' => $items,
            'invoices' => array_values($invoices->toArray()),
        ]);
    }

    public function invoices(Request $request)
    {
        Gate::authorize('viewAnyInOrganization', Invoice::class);

        $organization = OrganizationService::getOrganization($request);

        return Inertia::render('Invoices', [
            'organization' => $organization,
            'invoices' => Invoice::where('organization_id', $organization?->id)
                ->where('issue_at', '<=', now())
                ->whereNotNull('delivered_at')
                ->orderBy('number', 'desc')
                ->paginate(10)
        ]);
    }
}
