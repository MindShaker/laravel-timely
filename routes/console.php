<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Every Monday at 06:00 — email users with unfilled planning days in the next 2 weeks
Schedule::command('calendar:remind')->weeklyOn(1, '06:00');
