<?php

namespace App\Models;

use App\Notifications\AccountBalanceNotification;
use App\Notifications\AccountSummaryNotification;
use App\Notifications\CustomWelcomeNotification;
use App\Notifications\DailyDigestNotification;
use App\Notifications\InvoiceNotification;
use App\Notifications\InvoiceReminderNotification;
use App\Notifications\InvoicesCreatedNotification;
use App\Notifications\PaymentNotification;
use App\Notifications\PaymentIntentNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\WelcomeNotification\ReceivesWelcomeNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, ReceivesWelcomeNotification;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'preferences' => 'array',
    ];

    public $appends = [
        'first_name',
    ];

    /**
     * Send a password reset notification to the user.
     */
    // public function sendPasswordResetNotification($token)
    // {
    //     $url = config('app.url') . '/reset-password?token='.$token;

    //     $this->notify(new ResetPasswordNotification($url));
    // }

    /**
     * Send an invoice notification to the user.
     */
    public function sendInvoiceNotification(Invoice $invoice)
    {
        $this->notify(new InvoiceNotification($invoice));
    }

    public function sendInvoiceReminderNotification(Invoice $invoice)
    {
        if ($invoice->paid) {
            throw (new \Exception('Cannot send reminder. Invoice is marked as paid.'));
        }

        $this->notify(new InvoiceReminderNotification($invoice));
    }

    public function sendPaymentNotification(Payment $payment)
    {
        $this->notify(new PaymentNotification($payment));
    }

    public function sendWelcomeNotification(Carbon $validUntil)
    {
        $this->notify(new CustomWelcomeNotification($validUntil));
    }

    public function sendAccountBalanceNotification(Organization $organization)
    {
        $this->notify(new AccountBalanceNotification($organization));
    }

    public function sendAccountSummaryNotification(Organization $organization)
    {
        $this->notify(new AccountSummaryNotification($organization));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Organization>
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class);
    }

    public function getFirstNameAttribute()
    {
        return explode(' ', $this->name)[0];
    }

    public function sendDailyDigestNotification()
    {
        $this->notify(new DailyDigestNotification());
    }

    public function sendInvoicesCreatedNotification($invoiceCount)
    {
        $this->notify(new InvoicesCreatedNotification($invoiceCount));
    }

    public function sendPaymentIntentNotification($type, $paymentIntent)
    {
        $this->notify(new PaymentIntentNotification($type, $paymentIntent));
    }
}
