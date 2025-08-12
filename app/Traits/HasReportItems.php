<?php

namespace App\Traits;

use App\Models\ReportItem;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasReportItems
{
    public function initializeHasReportItems()
    {
        $this->append('minutes');
        $this->append('hours');
    }

    public function reportItems(): HasMany
    {
        return $this->hasMany(ReportItem::class);
    }

    public function getMinutesAttribute()
    {
        $minutes = 0;

        if ($this->reportItems->count()) {
            foreach ($this->reportItems as $reportItem) {
                $minutes += $reportItem->minutes;
            }
        }

        return $minutes;
    }

    public function getHoursAttribute()
    {
        return number_format($this->minutes / 60, 2);
    }
}
