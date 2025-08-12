<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'starts_at',
        'ends_at',
        'is_remote_interaction',
        'report_item_id',
        'issue_id',
        'organization_id',
        'user_id',
    ];

    public $appends = [
        'minutes',
    ];

    public function getMinutesAttribute()
    {
        if (! $this->starts_at || ! $this->ends_at) {
            return null;
        }

        return (int) round((new Carbon($this->starts_at))->diffInMinutes(new Carbon($this->ends_at)));
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function reportItem(): BelongsTo
    {
        return $this->belongsTo(ReportItem::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
