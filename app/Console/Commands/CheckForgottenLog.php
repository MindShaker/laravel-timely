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
            
            $hasRecord = logs::where('user_id', '=', $user->id, 'and')
                ->whereDate('data', $lastBusinessDay) 
                ->exists();

            if (!$hasRecord) {
                Mail::to($user->email)->send(new LembretePontoMail($lastBusinessDay->format('d/m/Y')));
                $this->info("Reminder sent to: " . $user->email);
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
