<?php

namespace App\Console\Commands;

use App\Mail\VacationDeadlineMail;
use App\Models\Absence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendVacationReminders extends Command
{
    protected $signature   = 'vacation:remind {--dry-run : List recipients without sending emails}';
    protected $description = 'Email non-admin users who have fewer than 22 vacation days planned for the current year';

    private const ALLOWANCE = 22;

    public function handle(): int
    {
        $year         = now()->year;
        $deadlineDate = Carbon::create($year, 3, 31)->isoFormat('D [de] MMMM [de] YYYY');
        $calendarUrl  = route('calendar');
        $dryRun       = $this->option('dry-run');
        $sent         = 0;

        $users = User::where('tipo', '!=', 'admin')->get();

        foreach ($users as $user) {
            $count = Absence::where('user_id', $user->id)
                ->where('type', 'vacation')
                ->whereYear('date', $year)
                ->count();

            if ($count >= self::ALLOWANCE) {
                $this->line("✓ {$user->name} — {$count} vacation days (complete)");
                continue;
            }

            $this->warn("→ {$user->name} — {$count}/" . self::ALLOWANCE . " vacation days");

            if ($dryRun) continue;

            Mail::to($user->email)->send(new VacationDeadlineMail(
                user:          $user,
                vacationCount: $count,
                allowance:     self::ALLOWANCE,
                deadlineDate:  $deadlineDate,
                calendarUrl:   $calendarUrl,
            ));
            $sent++;
        }

        $this->info($dryRun ? 'Dry run complete.' : "Reminders sent: {$sent}");
        return self::SUCCESS;
    }
}
