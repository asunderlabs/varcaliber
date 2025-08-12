<?php

namespace App\Notifications;

use App\Mail\AdminDailyDigest;
use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Spatie\ScheduleMonitor\Support\ScheduledTasks\ScheduledTasks;
use Spatie\ScheduleMonitor\Support\ScheduledTasks\Tasks\Task;

class DailyDigestNotification extends Notification
{
    use Queueable;

    const TIMEZONE = 'America/Chicago';

    private $stats;

    private $scheduleFailures = [];

    private $errors = [];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $tasks = ScheduledTasks::createForSchedule()
            ->uniqueTasks()
            ->filter(fn (Task $task) => $task->isBeingMonitored());

        if (! $tasks->isEmpty()) {
            foreach ($tasks as $task) {
                if (! $lastRunFailedAt = $task->lastRunFailedAt()) {
                    continue;
                }

                if ($lastRunFailedAt < now()->subDay()->toDateTimeString()) {
                    continue;
                }

                $this->scheduleFailures[] = "<b>{$task->name()}</b> last failed at " . $lastRunFailedAt->setTimezone(self::TIMEZONE)->toDateTimeString() . ' (' . self::TIMEZONE . ')';
            }
        }

        // $logViewer = new LaravelLogViewer();
        // $logs = $logViewer->all();

        // $logsLast24Hours = array_filter($logs, function ($entry) {
        //     return $entry['date'] >= now()->subDay()->toDateTimeString();
        // });

        $critical = [];
        $errors = [];
        $warnings = [];

        // foreach ($logsLast24Hours as $entry) {
        //     if ($entry['level'] === 'warning') {
        //         $warnings[] = $entry;

        //         continue;
        //     }

        //     if ($entry['level'] === 'error') {
        //         $errors[] = $entry;
        //     } elseif ($entry['level'] === 'critical') {
        //         $critical[] = $entry;
        //     } else {
        //         continue;
        //     }

        //     $this->errors[] = '[<b>' . (new Carbon($entry['date']))->setTimezone(self::TIMEZONE)->toDateTimeString() . '</b> (' . self::TIMEZONE . ') ] <span style="color: red">' . ucfirst($entry['level']) . "</span> - <i>{$entry['text']}</i>";
        // }

        // Limit to 10 errors
        // $this->errors = array_slice($this->errors, 0, 10);

        $this->stats = [
            'Disk Usage' => number_format(disk_free_space('/') / 1000000000, 2) . ' / ' . number_format(disk_total_space('/') / 1000000000, 2) . ' GB',
            // 'Log File Size' => number_format(app('files')->size($logViewer->getFiles()[0]) / 1000000, 2) . ' MB',
            // 'Log Entries' => count($logsLast24Hours) . ' in last 24 hours (' . count($logs) . ' total)',
            // 'Errors (last 24 hours)' => count($errors) + count($critical),
            // 'Warnings (last 24 hours)' => count($warnings),
            'Scheduled Task Failures (last 24 hours)' => count($this->scheduleFailures),
        ];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable)
    {
        $email = Email::create([
            'type' => 'Daily Digest',
            'subject' => 'Varcaliber Daily Digest',
            'to' => $notifiable->email,
            'status' => 'created',
        ]);

        return (new AdminDailyDigest($this->stats, $this->errors, $this->scheduleFailures))
            ->to($email->to)
            ->subject($email->subject)
            ->metadata('email_id', (string) $email->id);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
