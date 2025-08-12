<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Organization;
use App\Models\Report;
use App\Models\ReportItem;
use App\Models\WorkEntry;
use App\Services\OrganizationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class HoursController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', WorkEntry::class);

        $organization = OrganizationService::getOrganization($request);

        return Inertia::render('Admin/Hours', [
            'organization' => $organization,
            'workEntries' => WorkEntry::with('organization', 'issue', 'reportItem.report:id,name')
                ->when($organization, function ($query, $organization) {
                    $query->where('organization_id', $organization->id);
                })
                ->orderBy('starts_at', 'desc')
                ->paginate(10),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', WorkEntry::class);

        return Inertia::render('Admin/AddWork', [
            'organizations' => Organization::with('issues')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', WorkEntry::class);
        
        $validated = $request->validate([
            'description' => 'required',
            'startDate' => 'required',
            'startTime' => 'required',
            'endDate' => 'required',
            'endTime' => 'required',
            'organization' => 'required',
            'newIssue' => 'required|bool',
            'issue' => [
                Rule::requiredIf(function() use ($request) {
                    return $request->newIssue === false;
                }), 'nullable', 'integer'],
            'issueName' => 'required_if:newIssue,true|nullable|string|max:50',
        ]);

        DB::transaction(function() use ($request) {
            $newIssue = $request->newIssue ? Issue::create([
                    'title' => $request->issueName,
                    'organization_id' => $request->organization
                ]) : null;
    
            WorkEntry::create([
                'description' => $request->description,
                'starts_at' => (new Carbon($request->startDate . ' ' . $request->startTime, 'America/Chicago'))->setTimezone('UTC'),
                'ends_at' => (new Carbon($request->endDate . ' ' . $request->endTime, 'America/Chicago'))->setTimezone('UTC'),
                'issue_id' => $newIssue ? $newIssue->id : $request->issue,
                'organization_id' => $request->organization,
                'user_id' => auth()->user()->id,
            ]);
        });

        return redirect()->route('admin.hours.index')->with('message', 'Created work entry');
    }

    public function edit(WorkEntry $workEntry)
    {
        Gate::authorize('update', $workEntry);

        return Inertia::render('Admin/EditWork', [
            'workEntry' => $workEntry,
            'organizations' => Organization::with('issues')->get(),
        ]);
    }

    public function update(Request $request, WorkEntry $workEntry)
    {
        Gate::authorize('update', $workEntry);

        $validated = $request->validate([
            'description' => 'required',
            'startDate' => 'required',
            'startTime' => 'required',
            'endDate' => 'required',
            'endTime' => 'required',
            'organization' => 'required',
            'newIssue' => 'required|bool',
            'issue' => [
                Rule::requiredIf(function() use ($request) {
                    return $request->newIssue === false;
                }), 'nullable', 'integer'],
            'issueName' => 'required_if:newIssue,true|nullable|string|max:50',
        ]);

        DB::transaction(function() use ($request, $workEntry) {
            $newIssue = $request->newIssue ? Issue::create([
                'title' => $request->issueName,
                'organization_id' => $request->organization
            ]) : null;

            $workEntry->description = $request->description;
            $workEntry->starts_at = (new Carbon($request->startDate . ' ' . $request->startTime, 'America/Chicago'))->setTimezone('UTC');
            $workEntry->ends_at = (new Carbon($request->endDate . ' ' . $request->endTime, 'America/Chicago'))->setTimezone('UTC');
            $workEntry->issue_id = $newIssue ? $newIssue->id : $request->issue;
            $workEntry->organization_id = $request->organization;
            $workEntry->save();

            if ($workEntry->reportItem) {
                $oldMinutes = $workEntry->reportItem->minutes;
                $workEntry->reportItem->update([
                    'description' => $workEntry->description,
                    'minutes' => $workEntry->minutes,
                    'issue_id' => $workEntry->issue_id,
                ]);

                $workEntry->reportItem->report->update([
                    'minutes' => $workEntry->reportItem->report->minutes + ($workEntry->minutes - $oldMinutes)
                ]);
            }
        });

        return redirect()->route('admin.hours.index')->with('message', 'Work entry updated successfully!');
    }

    /**
     * Show page to report work
     */
    public function reportWorkCreate(WorkEntry $workEntry)
    {
        Gate::authorize('update', $workEntry);

        return Inertia::render('ReportWork', [
            'workEntry' => $workEntry,
            'reports' => Report::where('starts_at', '<=', $workEntry->starts_at)
                ->where('ends_at', '>=', $workEntry->starts_at)
                ->where('organization_id', $workEntry->organization_id)
                ->get()
        ]);
    }

    /**
     * Route to handle updating work resources to report work
     */
    public function reportWorkStore(Request $request, WorkEntry $workEntry)
    {
        Gate::authorize('update', $workEntry);

        $report = Report::findOrFail($request->report_id);

        // TODO: Add validation to check that work entry exists within report window and belongs 
        // to the same organization

        DB::transaction(function() use ($report, $workEntry) {
            $report->minutes += $workEntry->minutes;
            $report->save();
    
            $reportItem = ReportItem::create([
                'description' => $workEntry->description,
                'minutes' => $workEntry->minutes,
                'hourly_rate' => $workEntry->organization->hourly_rate,
                'issue_id' => $workEntry->issue_id,
                'report_id' => $report->id,
                'work_entry_id' => $workEntry->id,
            ]);
    
            $workEntry->report_item_id = $reportItem->id;
            $workEntry->save();
        });

        return redirect()->route('admin.hours.index')->with('message', 'Work reported successfully!');
    }

    public function unreport(WorkEntry $workEntry)
    {
        Gate::authorize('update', $workEntry);

        $workEntry->reportItem->delete();
        $workEntry->report_item_id = null;
        $workEntry->save();

        return back()->with('message', 'Work unreported successfully!');
    }

    public function destroy(WorkEntry $workEntry)
    {
        Gate::authorize('delete', $workEntry);

        if ($workEntry->reportItem) {
            $workEntry->reportItem->delete();
        }

        $workEntry->delete();

        return redirect()->route('admin.hours.index')->with('message', 'Work entry deleted');
    }
}
