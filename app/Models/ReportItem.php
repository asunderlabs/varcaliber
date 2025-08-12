<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'minutes',
        'hourly_rate',
        'fixed_amount',
        'is_remote_interaction',
        'issue_id',
        'report_id',
        'work_entry_id',
    ];

    public function workEntry(): HasOne
    {
        return $this->hasOne(WorkEntry::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
