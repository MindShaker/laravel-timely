<?php

namespace App\Console\Commands;

use App\Mail\CalendarReminderMail;
use App\Models\Absence;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCalendarReminders extends Command
{
    protected $signature   = 'calendar:remind {--dry-run : List unfilled days without sending emails}';
    protected $description = 'Email users who have unfilled planning days in the next two weeks';

    public function handle(): int
    {
        $today    = Carbon::today();
        $rangeEnd = $today->copy()->addDays(13); // 2 calendar weeks from today

        // Workdays in the range (Mon–Fri), excluding holidays
        $holidays = Holiday::whereBetween('date', [$today->toDateString(), $rangeEnd->toDateString()])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->flip()
            ->all();

        $workdays = collect();
        for ($d = $today->copy(); $d->lte($rangeEnd); $d->addDay()) {
            if (!$d->isWeekend() && !isset($holidays[$d->toDateString()])) {
                $workdays->push($d->toDateString());
            }
        }

        if ($workdays->isEmpty()) {
            $this->info('No workdays in the next two weeks.');
            return self::SUCCESS;
        }

        $users = User::all();
        $sent  = 0;

        foreach ($users as $user) {
            $filled = Absence::where('user_id', $user->id)
                ->whereBetween('date', [$today->toDateString(), $rangeEnd->toDateString()])
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->flip()
                ->all();

            $unfilled = $workdays->reject(fn($day) => isset($filled[$day]))->values()->all();

            if (empty($unfilled)) {
                $this->line("✓ {$user->name} — calendar complete");
                continue;
            }

            $this->warn("✗ {$user->name} — " . count($unfilled) . ' day(s) missing');

            if ($this->option('dry-run')) {
                foreach ($unfilled as $day) {
                    $this->line("    {$day}");
                }
                continue;
            }

            $calendarUrl = route('calendar.month', [
                Carbon::parse($unfilled[0])->year,
                Carbon::parse($unfilled[0])->month,
            ]);

            Mail::to($user->email)->send(new CalendarReminderMail($user, $unfilled, $calendarUrl));
            $sent++;
        }

        $this->info($this->option('dry-run') ? 'Dry run complete.' : "Reminders sent: {$sent}");
        return self::SUCCESS;
    }
}
