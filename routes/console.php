<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Define the scheduled tasks for the application. These tasks will be
| executed by the Laravel scheduler when running `php artisan schedule:run`.
|
*/

// Send scheduled notifications daily at 8:00 AM
Schedule::command('notifications:send-scheduled')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduled-notifications.log'));

// Check for overdue items every 4 hours
Schedule::command('notifications:send-scheduled --type=car')
    ->everyFourHours()
    ->withoutOverlapping()
    ->runInBackground();

// Check certificate expirations weekly on Monday
Schedule::command('notifications:send-scheduled --type=certificate')
    ->weeklyOn(1, '09:00')
    ->withoutOverlapping()
    ->runInBackground();

// Send reminder notifications every hour
Schedule::command('reminders:send')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/reminder-notifications.log'));
