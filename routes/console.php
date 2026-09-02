<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Every Monday at 06:00 — email users with unfilled planning days in the next 2 weeks
Schedule::command('calendar:remind')->weeklyOn(1, '06:00');

// Every Monday in February at 09:00 — remind users to plan vacations before 31 March
Schedule::command('vacation:remind')->weeklyOn(1, '09:00')->when(fn () => now()->month === 2);

// Jan 1 at 01:00 — pull next year's Portuguese holidays
Schedule::command('holidays:sync')->yearlyOn(1, 1, '01:00');
