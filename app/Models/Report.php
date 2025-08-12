<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'starts_at',
        'ends_at',
        'cached_items',
        'minutes',
        'organization_id',
        'report_type',
    ];

    protected $casts = [
        'cached_items' => 'array',
    ];

    public $appends = [
        'is_current',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ReportItem::class);
    }

    public function getIsCurrentAttribute()
    {
        return now() < (new Carbon($this->ends_at));
    }

    // Get billing cycle start and end datetimes
    // FIRST_BILLING_CYCLE must be set to non DST date (e.g. January not September)
    public static function billingCycle($localDate = null)
    {
        $date = empty($localDate) ? now() : (new Carbon($localDate, 'America/Chicago'))->setTimezone('UTC');

        $cycleStart = (new Carbon(config('app.first_billing_cycle'), 'America/Chicago'))->setTimezone('UTC');
        $cycle = [];

        $cycleDays = config('app.billing_cycle_frequency') === 'biweekly' ? 14 : 7;

        while (empty($cycle) || $cycleStart->format('Y') < 2100) {
            $start = $cycleStart->copy();
            $end = $cycleStart->copy()->addDays($cycleDays)->subSecond();

            // Check for different offset due to daylight savings ending or starting. Adjust end time.
            if ($start->copy()->setTimezone('America/Chicago')->utcOffset() !== $end->copy()->setTimezone('America/Chicago')->utcOffset()) {
                $end->addMinutes($start->copy()->setTimezone('America/Chicago')->utcOffset() - $end->copy()->setTimezone('America/Chicago')->utcOffset());
            }

            if ($date >= $start && $date <= $end) {
                $cycle = [
                    'start' => $start,
                    'end' => $end,
                ];
            }

            $cycleStart->addDays($cycleDays);
        }

        // Modify UTC time so that it always starts at midnight and ends at 11:59 PM in Chicago (accounts for DST)
        if ($cycle['start']->copy()->setTimezone('America/Chicago')->utcOffset() === -300) {
            $cycle['start']->subHour();
        }

        if ($cycle['end']->copy()->setTimezone('America/Chicago')->utcOffset() === -300) {
            $cycle['end']->subHour();
        }

        return $cycle;
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // Get the items as they should appear on the bill/invoice
    public function billItems()
    {
        $items = [];
        $itemizedItems = [];

        if ($this->items->count()) {

            $groupedItems = [];

            foreach ($this->items as $item) {
                $groupedItems[$item->hourly_rate][] = $item;
            }

            foreach ($groupedItems as $hourlyRate => $itemsInGroup) {

                // TODO: Check for fixed amount in addition to no hourly_rate
                if ($hourlyRate === 0) {
                    $itemizedItems = array_merge($itemizedItems, $itemsInGroup);

                    continue;
                }

                $issues = $this->getIssuesFromReportItems($itemsInGroup);

                $otherItems = array_filter($itemsInGroup, function ($item) {
                    return ! $item->issue;
                });

                $otherItems = array_map(function ($otherItem) {
                    return (object) [
                        'title' => $otherItem->description,
                        'minutes' => $otherItem->minutes,
                        'item' => $otherItem,
                    ];
                }, $otherItems);

                usort($otherItems, function ($a, $b) {
                    return $b->minutes <=> $a->minutes;
                });

                $largestFromEach = array_filter([
                    $issues[0] ?? null,
                    $otherItems[0] ?? null,
                ], function ($i) {
                    return $i !== null;
                });

                usort($largestFromEach, function ($a, $b) {
                    return $b->minutes <=> $a->minutes;
                });

                $hasOtherWork = (count($issues ?? []) + count($otherItems)) > 1;

                $items[] = [
                    'description' => rtrim(trim($largestFromEach[0]->title, '.')) . ($hasOtherWork ? ' and other work' : ''),
                    'minutes' => array_reduce($itemsInGroup, function ($carry, $item) {
                        $carry += $item->minutes;

                        return $carry;
                    }),
                    'hourly_rate' => $itemsInGroup[0]->hourly_rate,
                ];
            }
        }

        return array_merge($items, $itemizedItems);
    }

    private function getIssuesFromReportItems($items)
    {
        $issueItems = array_filter($items, function ($item) {
            return $item->issue;
        });

        if (empty($issueItems)) {
            return null;
        }

        $issueIds = array_unique(
            array_map(function ($item) {
                return $item->issue->id;
            }, $issueItems)
        );

        $issueIds = array_unique($issueIds);
        $issues = [];

        foreach ($issueIds as $issueId) {
            $issue = null;
            $minutes = 0;

            foreach ($issueItems as $issueItem) {
                if ($issueItem->issue->id === $issueId) {
                    $minutes += $issueItem->minutes;
                    $issue = $issueItem->issue;
                }
            }

            $issues[] = (object) [
                'title' => $issue->title,
                'minutes' => $minutes,
                'issue' => $issue,
            ];
        }

        usort($issues, function ($a, $b) {
            return $b->minutes <=> $a->minutes;
        });

        return $issues;
    }
}
