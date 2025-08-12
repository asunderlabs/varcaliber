<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SendAccountSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:sendSummary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send account summary emails.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Account Balance Email
        foreach (User::whereNotNull('preferences')->where('preferences->account_notification_email', true)->get() as $user) {

            $day = $user->preferences['account_notification_email_day'] ?? 'Sun';
            $day = ucwords(substr($day, 0, 3));

            if ($day !== now('America/Chicago')->format('D')) {
                continue;
            }

            foreach ($user->organizations as $organization) {
                if ($organization->billTotal() || $organization->issuedInvoices->where('paid', false)->count()) {
                    $user->sendAccountSummaryNotification($organization);
                }
            }
        }
    }
}
