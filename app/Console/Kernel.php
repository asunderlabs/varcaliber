<?php

namespace App\Console;

use App\Models\Email;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('invoice:generate')->timezone('America/Chicago')->sundays()->dailyAt('20:00');
        // $schedule->command('account:sendBalance')->timezone('America/Chicago')->dailyAt('9:00');
        $schedule->command('account:sendSummary')->timezone('America/Chicago')->dailyAt('9:00');

        if (config('app.env') === 'production') {
            $schedule->command('invoice:send')->everyFiveMinutes();
        }

        $schedule->command('model:prune', [
            '--model' => [MonitoredScheduledTaskLogItem::class, Email::class]
        ])->timezone('America/Chicago')->dailyAt('03:00')->monitorName('Prune Models');

        $schedule->call(function () {
            // Daily Digest (Administrative Email)
            foreach (User::where('is_admin', true)->get() as $user) {
                $user->sendDailyDigestNotification();
            }

        })->timezone('America/Chicago')->dailyAt('08:00')->monitorName('Admin Digest');
        

        $scheduledMaintenanceEndsAt = now()->setTimezone('America/Chicago')->setTimeFrom(config('app.scheduled_maintenance_start_time'))->addMinutes(config('app.scheduled_maintenance_duration_minutes'));

        // Enable scheduled maintenance message
        $schedule->call(function () use ($scheduledMaintenanceEndsAt) {
            $message = Setting::firstOrCreate([
                'key' => 'scheduled_maintenance_notification_message',
            ]);
            $start = now()->setTimezone('America/Chicago')->setTimeFrom(config('app.scheduled_maintenance_start_time'));
            $message->value = 'The system will be unavailable for a few minutes between ' . $start->format('g:i a') . ' and ' . $scheduledMaintenanceEndsAt->format('g:i a') . ' CT on ' . $start->format('l F, j') . ' for scheduled maintenance';
            $message->save();
            info('Added scheduled maintenace message');
        })->fridays()->timezone('America/Chicago')->at('0:00');

        // Disable scheduled maintenance message after some time
        $schedule->call(function () {
            $message = Setting::firstOrCreate([
                'key' => 'scheduled_maintenance_notification_message',
            ]);
            $message->value = null;
            $message->save();
            info('Removed scheduled maintenance message');
        })->fridays()->timezone('America/Chicago')->at($scheduledMaintenanceEndsAt->format('H:i'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
