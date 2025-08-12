<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\WorkEntry;
use Illuminate\Http\Request;

class DashboardService
{
    public static function userDashboard(Request $request)
    {
        $organization = OrganizationService::getOrganization($request);
        $billTotal = $organization->billTotal();
        $hours = $organization->hours();

        $invoices = $organization->issuedInvoices->reverse()->where('paid', false);

        $statCards = [
            [
                'title' => 'Current Period',
                'stat' => $billTotal ? '$' . number_format($billTotal / 100, 2) : '$0',
                'statLinkText' => 'View Billing Summary',
                'statLinkUrl' => route('billing.index'),
            ],
            [
                'title' => 'Work Reported',
                'stat' => number_format($hours, 2) . ' hrs',
                'statLinkText' => 'View Report',
                'statLinkUrl' => route('reports.index'),
            ],
            [
                'title' => 'Unpaid Invoices',
                'stat' => (string) $invoices->count(),
                'statLinkText' => 'View Invoices',
                'statLinkUrl' => route('invoices.index'),

            ],
        ];

        return [
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
            ],
            'statCards' => $statCards,
        ];
    }

    public static function adminDashboard(Request $request)
    {
        $workEntries = WorkEntry::whereNull('report_item_id')
            ->with('organization')
            ->get();

        if ($workEntries) {
            $unreportedMinutes = 0;
            foreach ($workEntries as $workEntry) {
                $unreportedMinutes += $workEntry->minutes;
            }
        }

        $billableTotal = 0;
        foreach (Organization::all() as $organization) {
            $billableTotal += $organization->billTotal();
        }

        $invoicesTotal = Invoice::where('paid', 0)->sum('total');

        $statCards = [
            [
                'title' => 'Unreported Work',
                'stat' => ($unreportedMinutes ? number_format($unreportedMinutes / 60, 1) : '0') . ' hrs',
                'statLinkText' => 'View Reports',
                'statLinkUrl' => route('admin.hours.index'),
            ],
            [
                'title' => 'Accounts Billable',
                'stat' => $billableTotal ? '$' . number_format($billableTotal / 100, 2) : '$0',
                'statLinkText' => 'View Reports',
                'statLinkUrl' => route('admin.hours.index'),
            ],
            [
                'title' => 'Unpaid Invoices',
                'stat' => $invoicesTotal ? '$' . number_format($invoicesTotal / 100, 2) : '$0',
                'statLinkText' => 'View Invoices',
                'statLinkUrl' => route('admin.invoices.index'),
            ],
        ];

        return [
            'statCards' => $statCards,
        ];
    }
}
