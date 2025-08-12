<?php

namespace App\Console\Commands;

use App\Models\Issue;
use App\Models\Project;
use App\Models\ReportItem;
use App\Models\ReportItemCategory;
use App\Models\WorkEntry;
use Illuminate\Console\Command;

class ConvertReportItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-report-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert report items that are not issues or phone calls';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->convertProjectReportItemsToIssues();
        $this->convertOtherReportItemsToIssues();
        $this->markRemoteInteractions();
        $this->checkForInvalidReportItems();

        $this->line("Done");
    }

    private function convertProjectReportItemsToIssues()
    {
        $reportItems = ReportItem::whereNotNull('project_id')->get();
        $workEntries = WorkEntry::whereNotNull('project_id')->get();
        $projectIds = $reportItems->pluck('project_id')->unique()->merge($workEntries->pluck('project_id')->unique())->unique()->values();
        
        $projects = Project::whereIn('id', $projectIds)->get();

        if (!$projects->count()) {
            $this->warn('There are no report item projects to convert');
            return;
        }
        
        $this->line("Converting {$projectIds->count()} report item projects...");

        // Map project to issues (key: project_id, value: issue_id)
        $issueMap = [];

        foreach ($projects as $project) {
            $issue = Issue::create([
                'title' => $project->title,
                'organization_id' => $project->organization_id,
                'archived_at' => $project->archived_at,
            ]);

            $issueMap[$project->id] = $issue->id;
        }

        foreach ($reportItems as $reportItem) {
            $reportItem->update([
                'project_id' => null,
                'issue_id' => $issueMap[$reportItem->project_id]
            ]);
        }

        foreach ($workEntries as $workEntry) {
            $workEntry->update([
                'project_id' => null,
                'issue_id' => $issueMap[$workEntry->project_id]
            ]);
        }

        Project::truncate();
    }

    private function convertOtherReportItemsToIssues()
    {
        $reportItemCategoryIds = ReportItemCategory::whereIn('name', ['Research and Planning', 'Issues'])->pluck('id')->values();
        $reportItems = ReportItem::whereIn('report_item_category_id', $reportItemCategoryIds)->with('workEntry')->get();

        if (!$reportItems->count()) {
            $this->warn('There are no other report items to convert');
            return;
        }

        $this->line("Converting {$reportItems->count()} other report items...");

        foreach($reportItems as $reportItem) {

            if (!$reportItem->workEntry) {
                $this->warn('Missing work entry for report item ' . $reportItem->id);
                continue;
            }

            $issue = Issue::create([
                'title' => $reportItem->description,
                'organization_id' => $reportItem->workEntry->organization_id,
                'archived_at' => $reportItem->updated_at,
            ]);

            $reportItem->update([
                'issue_id' => $issue->id,
                'report_item_category_id' => null,
            ]);

            $reportItem->workEntry->update([
                'issue_id' => $issue->id,
                'report_item_category_id' => null,
            ]);
        }
    }

    private function markRemoteInteractions()
    {
        $reportItemCategoryIds = ReportItemCategory::where('name', 'Phone Calls')->pluck('id')->values();
        $reportItems = ReportItem::whereIn('report_item_category_id', $reportItemCategoryIds)->with('workEntry')->get();

        if (!$reportItems->count()) {
            $this->warn('There are no report items to mark as remote interactions');
            return;
        }

        $this->line("Marking {$reportItems->count()} report items as remote interactions...");

        foreach($reportItems as $reportItem) {
            $reportItem->update([
                'is_remote_interaction' => true,
                'report_item_category_id' => null,
            ]);

            $reportItem->workEntry?->update([
                'is_remote_interaction' => true,
                'report_item_category_id' => null,
            ]);
        }
    }

    private function checkForInvalidReportItems()
    {
        if ($count = ReportItem::whereNull('issue_id')->where('is_remote_interaction', false)->count()) {
            $this->error("There are {$count} report items that do not have an issue id");
        }

        if ($count = WorkEntry::whereNull('issue_id')->where('is_remote_interaction', false)->count()) {
            $this->error("There are {$count} work entries that do not have an issue id");
        }
    }
}
