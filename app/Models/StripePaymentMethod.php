<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StripePaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method_id',
        'name',
        'type',
        'last4',
        'exp_month',
        'exp_year',
        'organization_id',
    ];

    public $appends = [
        'expired',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getExpiredAttribute()
    {
        if (! $this->exp_month && ! $this->exp_year) {
            return false;
        }
        $month = str_pad((string) $this->exp_month, 2, '0', STR_PAD_LEFT);
        $expirationDate = (new Carbon("{$this->exp_year}-{$month}-01", 'America/Chicago'))->addMonth();

        return $expirationDate < now();
    }
}
