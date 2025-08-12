<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Email extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'type',
        'subject',
        'to',
        'cc',
        'status',
        'mailgun_timestamp',
        'mailgun_message_id',
        'organization_id',
    ];

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subMonths(3));
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
