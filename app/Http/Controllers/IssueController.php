<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Organization;
use App\Models\ReportItem;
use App\Models\WorkEntry;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Issue::class);

        $organization = OrganizationService::getOrganization($request);

        return Inertia::render('Admin/Issues', [
            'organization' => $organization,
            'issues' => Issue::with('organization')
                ->when($organization, function ($query, $organization) {
                    $query->where('organization_id', $organization->id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Issue::class);

        return Inertia::render('Admin/CreateOrEditIssue', [
            'organizations' => Organization::all(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Issue::class);

        $validated = $request->validate([
            'title' => 'required',
            'organization_id' => 'required',
        ]);

        Issue::create([
            'title' => $validated['title'],
            'organization_id' => $validated['organization_id'],
        ]);

        return redirect()->route('admin.issues.index')->with('message', 'Issue created successfully!');
    }

    public function edit(Issue $issue)
    {
        Gate::authorize('update', $issue);

        return Inertia::render('Admin/CreateOrEditIssue', [
            'issue' => $issue
        ]);
    }

    public function update(Request $request, Issue $issue)
    {
        Gate::authorize('update', $issue);

        $validated = $request->validate([
            'title' => 'required',
        ]);

        $issue->update([
            'title' => $validated['title']
        ]);

        return back()->with('message', 'Issue updated successfully!');
    }

    public function destroy(Issue $issue)
    {
        Gate::authorize('delete', $issue);

        DB::transaction(function() use ($issue) {
            ReportItem::where('issue_id', $issue->id)->delete();
            WorkEntry::where('issue_id', $issue->id)->delete();
            $issue->delete();
        });

        return redirect()->route('admin.issues.index')->with('message', 'Issue deleted successfully!');
    }

    public function archive(Issue $issue)
    {
        Gate::authorize('update', $issue);

        $issue->archived_at = now();
        $issue->save();
        
        return back()->with('message', 'Issue archived successfully!');
    }

    public function unarchive(Issue $issue)
    {
        Gate::authorize('update', $issue);

        $issue->archived_at = null;
        $issue->save();
        
        return back()->with('message', 'Issue unarchived successfully!');
    }
}
