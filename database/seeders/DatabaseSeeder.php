<?php

namespace Database\Seeders;

use App\Models\Email;
use App\Models\Invoice;
use App\Models\Issue;
use App\Models\Organization;
use App\Models\Report;
use App\Models\ReportItem;
use App\Models\User;
use App\Models\WorkEntry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    const ORGANIZATIONS_COUNT = 2;

    const USERS_PER_ORGANIZATIONS_COUNT = 2;

    const FIRST_BILLING_START = '2021-09-13';

    const FIRST_BILLING_END = '2021-09-21';

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'test@example.com',
            'password' => '$2y$10$NvizF6vU57yehkN6GlV91eIK751WfoDNwfzTNZyAD0izdlMbC.XCa', // 12341234
            'is_admin' => true,
        ]);

        $organizations = Organization::factory()->count(self::ORGANIZATIONS_COUNT)->create();

        foreach ($organizations as $organization) {

            // Create users
            for ($j = 0; $j < self::USERS_PER_ORGANIZATIONS_COUNT; $j++) {
                $user = User::factory()->make([
                    'password' => '$2y$10$NvizF6vU57yehkN6GlV91eIK751WfoDNwfzTNZyAD0izdlMbC.XCa', // 12341234
                    'preferences' => [
                        'invoice_notification_email' => $j === 0 ? true : false,
                        'invoice_notification_email_cc' => $j === 0 ? false : true,
                    ],
                ]);

                echo "Creating $user->email\n";

                $organization->users()->save($user);
            }

            // Create issues x5

            $issues = Issue::factory()->count(5)->make();
            $issues = $organization->issues()->saveMany($issues);

            // Create reports
            $billingCycle = Report::billingCycle();
            $reportDate = new Carbon($billingCycle['start']);

            for ($r = 0; $r < 12; $r++) {

                $report = new Report([
                    'starts_at' => $reportDate->copy(),
                    'ends_at' => $reportDate->copy()->addDays(14)->subSecond(),
                    'minutes' => random_int(1, 80) * 15,
                ]);

                $report = $organization->reports()->save($report);
                $reportDate->subDays(14);
            }

            // Create work items
            foreach ($organization->reports as $report) {

                $reportItems = [];

                $reportItems[] = ReportItem::factory()->make([
                    'report_id' => $report->id,
                    'issue_id' => $issues[array_rand($issues->toArray())]->id,
                    'hourly_rate' => $organization->hourly_rate,
                ]);

                $reportItems[] = ReportItem::factory()->make([
                    'report_id' => $report->id,
                    'issue_id' => $issues[array_rand($issues->toArray())]->id,
                    'hourly_rate' => $organization->hourly_rate,
                ]);

                $reportItems[] = ReportItem::factory()->make([
                    'report_id' => $report->id,
                    'issue_id' => $issues[array_rand($issues->toArray())]->id,
                    'hourly_rate' => $organization->hourly_rate,
                ]);

                $reportItems[] = ReportItem::factory()->make([
                    'report_id' => $report->id,
                    'hourly_rate' => $organization->hourly_rate,
                ]);

                $reportItems[] = ReportItem::factory()->make([
                    'report_id' => $report->id,
                    'hourly_rate' => $organization->hourly_rate,
                ]);

                $reportItems[] = ReportItem::factory()->make([
                    'report_id' => $report->id,
                    'hourly_rate' => $organization->hourly_rate,
                ]);

                foreach ($reportItems as $reportItem) {
                    $reportItem->save();
                    $start = (new Carbon($report->starts_at))->addDays(random_int(0, 6))->addMinutes(random_int(0, 120));
                    $workEntry = WorkEntry::create([
                        'description' => $reportItem->description,
                        'starts_at' => $start->toDateTimeString(),
                        'ends_at' => $start->addMinutes($reportItem->minutes)->toDateTimeString(),
                        'report_item_id' => $reportItem->id,
                        'organization_id' => $organization->id,
                        'user_id' => 1,
                    ]);
                    $reportItem->work_entry_id = $workEntry->id;
                    $reportItem->save();
                }
            }

            // Create invoices

            $reports = Report::where('organization_id', $organization->id)
                ->where('ends_at', '<', now()->toDateString())
                ->orderBy('starts_at')
                ->get();

            $invoices = [];

            foreach ($reports as $key => $report) {

                $items = $report->billItems();
                $subtotal = 0;

                foreach ($items as &$item) {
                    $item['hours'] = round($item['minutes'] / 60 * 100) / 100;
                    $item['amount'] = $item['hours'] * $item['hourly_rate'] * 100;
                    $subtotal += $item['amount'];
                    unset($item['minutes']);
                }

                $issueAt = (new Carbon($report->ends_at))->addDay()->setTimezone('America/Chicago');
                $issueAt = (new Carbon($issueAt->toDateString() . ' ' . config('app.issue_invoice_at_time'), 'America/Chicago'))->setTimezone('UTC');

                $dueAt = (new Carbon($report->ends_at))->addDays(config('app.invoice_due_after_days'))->setTimezone('America/Chicago');
                $dueAt = (new Carbon($dueAt->toDateString() . ' ' . config('app.invoice_due_at_time'), 'America/Chicago'))->setTimezone('UTC');

                $invoices[] = Invoice::create([
                    'number' => $key + 1,
                    'billing_start' => $report->starts_at,
                    'billing_end' => $report->ends_at,
                    'issue_at' => $issueAt,
                    'approved_at' => $key === ($organization->reports->count() - 1) ? null : (new Carbon($report->starts_at))->addDays(14), // All approved and delivered except last
                    'delivered_at' => $key === ($organization->reports->count() - 1) ? null : $issueAt, // All approved and delivered except last
                    'due_at' => $dueAt,
                    'paid' => $key < ($organization->reports->count() - 3), // All paid except last 3
                    'client_info' => [
                        'contact_name' => $organization->billing_contact,
                        'business_name' => $organization->name,
                        'address_line_1' => $organization->address_line_1,
                        'address_line_2' => $organization->address_line_2,
                        'city' => $organization->city,
                        'state' => $organization->state,
                        'zip_code' => $organization->zip_code,
                    ],
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'tax' => 0,
                    'total' => $subtotal,
                    'organization_id' => $organization->id,
                ]);

                foreach ($invoices as $invoice) {
                    $invoice->createPDF();
                }
            }

            // Create payments x2

            foreach ($invoices as $invoice) {

                if (! $invoice->paid) {
                    continue;
                }

                $invoice->payments()->create([
                    'paid_at' => (new Carbon($invoice->due_at))->subDays(5)->addDays(random_int(0, 10))->toDateTimeString(),
                    // 'invoice_id' => $invoice->id,
                    'amount' => $invoice->total,
                    'organization_id' => $organization->id,
                ]);
            }
        }

        Email::create([
            'type' => 'test_email',
            'subject' => 'Email 1 Subject Goes Here',
            'to' => 'test@example.com',
            'cc' => 'test1@example.com, test2@example.com',
            'status' => 'Created',
            'mailgun_timestamp' => null,
            'mailgun_message_id' => null,
            'organization_id' => 1,
        ]);

        Email::create([
            'type' => 'test_email',
            'subject' => 'Email 2 Subject Goes Here',
            'to' => 'test@example.com',
            'cc' => 'test1@example.com, test2@example.com',
            'status' => 'Created',
            'mailgun_timestamp' => null,
            'mailgun_message_id' => null,
            'organization_id' => 1,
        ]);

        Email::create([
            'type' => 'test_email',
            'subject' => 'Email 2 Subject Goes Here',
            'to' => 'test@example.com',
            'cc' => 'test1@example.com, test2@example.com',
            'status' => 'Created',
            'mailgun_timestamp' => null,
            'mailgun_message_id' => null,
            'organization_id' => 1,
        ]);
    }
}
