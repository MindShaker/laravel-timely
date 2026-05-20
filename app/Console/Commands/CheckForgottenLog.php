<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\logs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LembretePontoMail;

class CheckForgottenLog extends Command
{
    protected $signature = 'timeclock:check-reminders';
    protected $description = 'Sends reminders for missing timeclock entries';

    public function handle()
    {
        $today = Carbon::today();
        if ($today->isWeekend() || $this->isHoliday($today)) {
            return;
        }
        $lastBusinessDay = $today->copy()->subDay();
        while ($lastBusinessDay->isWeekend() || $this->isHoliday($lastBusinessDay)) {
            $lastBusinessDay->subDay();
        }

        $users = User::where('tipo', '=', 'user', 'and')->get();

        foreach ($users as $user) {
            $logsForDay = Logs::where('user_id', '=', $user->id,'and')
                ->whereDate('data', $lastBusinessDay)
                ->get();

            $shouldSendReminder = false;

            if ($logsForDay->isEmpty()) {
                $shouldSendReminder = true;
            } else {
                $hasValidExit = $logsForDay->contains(function ($l) {
                    $saida = trim((string) $l->saida);
                    if ($saida === '' || $saida === '00:00' || $saida === '00:00:00' || $saida === '0' || $saida === null) {
                        return false;
                    }
                    return true;
                });

                if (! $hasValidExit) {
                    $shouldSendReminder = true;
                }
            }

            if ($shouldSendReminder) {
                if (filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    try {
                         Mail::to($user->email)->send(new LembretePontoMail($lastBusinessDay->format('d/m/Y')));
                        $this->info("Reminder sent to: " . $user->email);
                    } catch (\Exception $e) {
                        $this->error("Failed to send to: " . $user->email . " | Error: " . $e->getMessage());
                    }
                } else {
                     $this->error("Invalid email format for User ID {$user->id}: " . $user->email);
                }
            }
        }
    }

    private function isHoliday($date)
    {
        $year = $date->year;
        $monthDay = $date->format('m-d');

        $fixedHolidays = [
            '01-01',
            '04-25',
            '05-01',
            '06-10',
            '08-15',
            '10-05',
            '11-01',
            '12-01',
            '12-08',
            '12-25'
        ];

        if (in_array($monthDay, $fixedHolidays)) return true;

        $easterDays = easter_days($year);
        $easter = Carbon::createFromDate($year, 3, 21)->addDays($easterDays);

        $goodFriday = $easter->copy()->subDays(2);
        $corpusChristi = $easter->copy()->addDays(60);

        return $date->isSameDay($goodFriday) || $date->isSameDay($corpusChristi);
    }
}
