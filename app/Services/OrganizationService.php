<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\ReportItem;
use App\Models\WorkEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrganizationService
{
    public static function userCanAccessOrganization(Organization $organization)
    {
        return auth()->user()->is_admin || auth()->user()->organizations->first()->id === $organization->id;
    }

    public static function getOrganization(Request $request): Organization|null
    {
        $organization = null;

        // Allow admin to specify the organization using a query string parameter
        if (auth()->user()->is_admin && $request->has('organization')) {
            return Organization::findOrFail($request->organization);
        }

        if (! auth()->user()->is_admin) {
            $organization = auth()->user()->organizations->first();
        } elseif (session('activeOrganization')) {
            $organization = Organization::findOrFail(session('activeOrganization'));
        }

        return $organization;
    }

    public static function organizationStats(Organization $organization)
    {
        $workEntries = WorkEntry::where('organization_id', $organization->id)->whereNotNull('report_item_id')->get();
        $reportItems = ReportItem::whereNotNull('minutes')->with('report')->get()->filter(function ($reportItem) use ($organization) {
            return $reportItem->report && $reportItem->report->organization_id === $organization->id;
        });

        $longestGaps = [];
        $averageGaps = [];
        $averageHoursByDuration = [];
        $averageEarningsByDuration = [];

        $durations = [3, 6, 12];

        foreach ($durations as $key => $duration) {

            $startDate = now()->submonths($duration)->toDateString();

            $entriesInPeriod = $workEntries->where('starts_at', '>=', $startDate);
            $longestGap = 0;
            $gaps = [];
            $previousEntry = null;

            foreach ($entriesInPeriod as $entry) {
                if ($previousEntry) {
                    $daysDiff = (int) round((new Carbon($entry->starts_at))->diffInDays(new Carbon($previousEntry->starts_at)));
                    $currentGap = $daysDiff > 1 ? $daysDiff - 1 : 0;
                    $longestGap = $currentGap > $longestGap ? $currentGap : $longestGap;
                    if ($currentGap) {
                        $gaps[] = $currentGap;
                    }
                }
                $previousEntry = $entry;
            }

            $longestGaps[] = $longestGap;
            $averageGaps[] = $gaps ? round(array_sum($gaps) / count($gaps)) : 0;

            $reportItemsInPeriod = $reportItems->where('created_at', '>=', $startDate);
            $totalMinutes = 0;
            $totalEarnings = 0;

            foreach ($reportItemsInPeriod as $reportItem) {
                $totalMinutes += $reportItem->minutes;
                $totalEarnings += $reportItem->minutes / 60 * $reportItem->hourly_rate;
            }

            $averageHoursByDuration[] = round($totalMinutes / 60 / $duration);
            $averageEarningsByDuration[] = round($totalEarnings / $duration);
        }

        $totalAmountFromInvoices = 0;

        foreach ($organization->invoices as $invoice) {
            $totalAmountFromInvoices += $invoice->total;
        }

        $firstInvoiceDate = $organization->invoices->sortBy('number')->first()?->issue_at;
        $monthsSinceFirstInvoice = (int) round((new Carbon($firstInvoiceDate))->diffInMonths(now()));

        return [
            [
                'name' => 'Longest Gap (3 months, 6 months, 12 months)',
                'text' => implode(', ', array_map(function ($gap) {
                    return $gap . ($gap === 1 ? ' day' : ' days');
                }, $longestGaps)),
            ],
            [
                'name' => 'Average Gap (3 months, 6 months, 12 months)',
                'text' => implode(', ', array_map(function ($gap) {
                    return $gap . ($gap === 1 ? ' day' : ' days');
                }, $averageGaps)),
            ],
            [
                'name' => 'Average Hours Per Month (3 months, 6 months, 12 months)',
                'text' => implode(', ', array_map(function ($hours) {
                    return $hours . ($hours === 1 ? ' hour' : ' hours');
                }, $averageHoursByDuration)),
            ],
            [
                'name' => 'Average Earnings From Hourly Work Per Month (3 months, 6 months, 12 months)',
                'text' => implode(', ', array_map(function ($amount) {
                    return '$' . number_format($amount);
                }, $averageEarningsByDuration)),
            ],
            [
                'name' => 'Months Since First Invoice',
                'text' => $monthsSinceFirstInvoice,
            ],
            [
                'name' => 'Average Monthly Earnings',
                'text' => '$' . ($monthsSinceFirstInvoice ? number_format(round($totalAmountFromInvoices / 100 / $monthsSinceFirstInvoice)) : 0),
            ],
            [
                'name' => 'Total Amount from Invoices',
                'text' => '$' . number_format($totalAmountFromInvoices / 100),
            ],
        ];
    }

    public static function createStripeCustomer(Organization $organization): bool
    {
        if ($organization->stripe_customer_id) {
            return false;
        }

        \Stripe\Stripe::setApiKey(config('app.stripe_key'));

        $customer = \Stripe\Customer::create(
            [
                'name' => $organization->name,
                'email' => $organization->email,
            ]
        );

        $organization->stripe_customer_id = $customer->id;
        $organization->save();

        return true;
    }
}
