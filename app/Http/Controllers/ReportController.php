<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\Organization;
use App\Models\Report;
use App\Models\WorkEntry;
use App\Services\OrganizationService;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Get list of current and past reports.
     * 
     * Previously showed either one report per page (either current billing period or past billing period)
     * 
     */
    public function index(Request $request)
    {   
        if (Gate::denies('viewAny', Report::class) && Gate::denies('viewAnyInOrganization', Report::class)) {
            abort(403);
        }

        $organization = OrganizationService::getOrganization($request);

        $currentReports = Report::when($organization, function (Builder $query, Organization $organization) {
                $query->where('organization_id', $organization->id);
            })
            ->with('items', 'items.issue', 'items.workEntry', 'organization')
            ->where('ends_at', '>', now())
            ->with('items')
            ->orderBy('ends_at', 'desc')
            ->get();

        $pastReports = Report::when($organization, function (Builder $query, Organization $organization) {
                $query->where('organization_id', $organization->id);
            })
            ->with('items', 'items.issue', 'items.workEntry', 'organization')
            ->where('ends_at', '<=', now())
            ->with('items')
            ->orderBy('ends_at', 'desc')
            ->paginate(5);

        return Inertia::render('Reports', [
            'workEntries' => WorkEntry::whereNull('report_item_id')
                ->with('organization', 'issue')
                ->when($organization, function (Builder $query, Organization $organization) {
                    $query->where('organization_id', $organization->id);
                })
                ->orderBy('starts_at', 'asc')
                ->get(),
            'organization' => $organization,
            'currentReports' => $currentReports,
            'pastReports' => $pastReports,
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Report::class);

        $organization = OrganizationService::getOrganization($request);
        return Inertia::render('CreateOrEditReport', [
            'organization' => $organization,
            'organizations' => Organization::all(),
        ]);
    }

    public function store(ReportRequest $request)
    {
        Gate::authorize('create', Report::class);

        $validated = $request->validated();
        $validated['starts_at'] = now('America/Chicago')->setDateFrom($validated['starts_at'])->startOfDay()->utc();
        $validated['ends_at'] = now('America/Chicago')->setDateFrom($validated['ends_at'])->endOfDay()->utc();
        Report::create($validated);

        return redirect()->route('admin.reports.index')->with('message', 'Report created');
    }

    public function edit(Report $report)
    {
        Gate::authorize('update', $report);

        return Inertia::render('CreateOrEditReport', [
            'organization' => $report->organization,
            'report' => $report,
            'organizations' => Organization::all(),
        ]);
    }

    public function update(ReportRequest $request, Report $report)
    {
        Gate::authorize('update', $report);

        $validated = $request->validated();

        $report->update($validated);

        return back()->with('message', 'Report updated');
    }
    
    public function destroy(Report $report)
    {
        Gate::authorize('delete', $report);
        
        DB::transaction(function() use ($report) {
            $workEntryIds = $report->items->map(function($item) {
                return $item->work_entry_id;
            })->unique();

            $report->items()->delete();
            WorkEntry::whereIn('id', $workEntryIds)->delete();
            $report->delete();
        });

        return redirect()->route('admin.reports.index')->with('message', 'Report deleted');
    }

}
