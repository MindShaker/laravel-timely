<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function show(Request $request, int $year = null, int $month = null)
    {
        $year  = $year  ?? now()->year;
        $month = $month ?? now()->month;

        return $this->buildCalendarView(Auth::user(), $year, $month, 'calendar.show', $request->boolean('all'));
    }

    public function adminShow(User $user, int $year = null, int $month = null)
    {
        $year  = $year  ?? now()->year;
        $month = $month ?? now()->month;

        return $this->buildCalendarView($user, $year, $month, 'admin.calendar');
    }

    public function markRange(Request $request)
    {
        $request->validate([
            'start'  => 'required|date_format:Y-m-d',
            'end'    => 'required|date_format:Y-m-d',
            'type'   => 'required|in:vacation,client,internal,undefined,training,absent',
            'remote' => 'boolean',
        ]);

        return $this->saveRange(Auth::user(), $request->start, $request->end,
                                $request->type, $request->boolean('remote'));
    }

    public function adminMarkRange(Request $request, User $user)
    {
        $request->validate([
            'start'  => 'required|date_format:Y-m-d',
            'end'    => 'required|date_format:Y-m-d',
            'type'   => 'required|in:vacation,client,internal,undefined,training,absent',
            'remote' => 'boolean',
        ]);

        return $this->saveRange($user, $request->start, $request->end,
                                $request->type, $request->boolean('remote'));
    }

    public function remove(string $date)
    {
        return $this->deleteDay(Auth::user(), $date);
    }

    public function adminRemove(User $user, string $date)
    {
        return $this->deleteDay($user, $date);
    }

    public function removeRange(Request $request)
    {
        $request->validate([
            'start' => 'required|date_format:Y-m-d',
            'end'   => 'required|date_format:Y-m-d',
            'type'  => 'required|in:vacation,client,internal,undefined,training,absent',
        ]);

        return $this->deleteDateRange(Auth::user(), $request->start, $request->end, $request->type);
    }

    public function adminRemoveRange(Request $request, User $user)
    {
        $request->validate([
            'start' => 'required|date_format:Y-m-d',
            'end'   => 'required|date_format:Y-m-d',
            'type'  => 'required|in:vacation,client,internal,undefined,training,absent',
        ]);

        return $this->deleteDateRange($user, $request->start, $request->end, $request->type);
    }

    public function yearOverview(int $year)
    {
        $types = ['vacation', 'client', 'internal', 'undefined', 'training', 'absent'];

        $absences = Absence::with('user')
            ->whereYear('date', $year)
            ->whereIn('type', $types)
            ->get();

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = [
                'types' => array_fill_keys($types, ['days' => 0, 'people' => []]),
                'total' => 0,
            ];
        }

        $yearTotals = array_fill_keys($types, ['days' => 0, 'people' => []]);

        foreach ($absences as $absence) {
            $m    = $absence->date->month;
            $type = $absence->type;

            $months[$m]['types'][$type]['days']++;
            $months[$m]['types'][$type]['people'][$absence->user_id] = $absence->user->name;
            $months[$m]['total']++;

            $yearTotals[$type]['days']++;
            $yearTotals[$type]['people'][$absence->user_id] = true;
        }

        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',    4 => 'Abril',
            5 => 'Maio',    6 => 'Junho',     7 => 'Julho',     8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return view('calendar.overview', compact('year', 'months', 'monthNames', 'yearTotals'));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildCalendarView(User $user, int $year, int $month, string $view, bool $showAll = false)
    {
        $firstDay    = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startOffset = $firstDay->dayOfWeek; // Sun=0 … Sat=6

        // Status days for Alpine (full year, all types)
        $yearStatusDays = $this->yearStatusDays($user, $year);

        // Vacation days this month — still needed for $vacationCount stat
        $vacationDays = array_filter($yearStatusDays, fn($s) =>
            $s['type'] === 'vacation'
            && str_starts_with($s['date'], sprintf('%04d-%02d', $year, $month))
        );

        // Holidays this month (date => name)
        $holidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->mapWithKeys(fn($h) => [$h->date->format('Y-m-d') => $h->name])
            ->toArray();

        // User's birthday in this month (computed, not stored)
        $birthdayDates = [];
        if ($user->birthdate) {
            $bday = $user->birthdate->setYear($year);
            if ((int) $bday->format('m') === $month) {
                $birthdayDates[] = $bday->format('Y-m-d');
            }
        }

        // Stats for the month
        $totalWorkdays = 0;
        $holidayCount  = count($holidays);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $dow     = (int) date('w', mktime(0, 0, 0, $month, $d, $year));
            if ($dow !== 0 && $dow !== 6 && !isset($holidays[$dateStr]) && !in_array($dateStr, $birthdayDates)) {
                $totalWorkdays++;
            }
        }
        $vacationCount     = count($vacationDays);
        $workedDays        = max(0, $totalWorkdays - $vacationCount);
        $yearVacationCount = Absence::where('user_id', $user->id)
            ->where('type', 'vacation')
            ->whereYear('date', $year)
            ->count();
        $vacationAllowance = 22;

        // Other users' statuses for the month (date => [{name, initials, type, remote}])
        $othersStatus    = [];
        $othersBirthdays = [];
        if ($showAll) {
            Absence::with('user')
                ->whereIn('type', ['vacation', 'client', 'internal', 'undefined', 'training', 'absent'])
                ->where('user_id', '!=', $user->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->each(function ($absence) use (&$othersStatus) {
                    $othersStatus[$absence->date->format('Y-m-d')][] = [
                        'name'     => $absence->user->name,
                        'initials' => $this->initials($absence->user->name),
                        'type'     => $absence->type,
                        'remote'   => (bool) $absence->remote,
                    ];
                });

            User::where('id', '!=', $user->id)
                ->whereNotNull('birthdate')
                ->whereMonth('birthdate', $month)
                ->get()
                ->each(function ($u) use (&$othersBirthdays, $year) {
                    $dateStr = $u->birthdate->copy()->setYear($year)->format('Y-m-d');
                    $othersBirthdays[$dateStr][] = [
                        'name'     => $u->name,
                        'initials' => $this->initials($u->name),
                    ];
                });
        }

        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return view($view, compact(
            'user', 'year', 'month', 'daysInMonth', 'startOffset',
            'yearStatusDays', 'holidays', 'birthdayDates',
            'vacationCount', 'holidayCount', 'workedDays',
            'yearVacationCount', 'vacationAllowance',
            'prevMonth', 'nextMonth', 'monthNames',
            'showAll', 'othersStatus', 'othersBirthdays'
        ));
    }

    private function saveRange(User $user, string $startStr, string $endStr, string $type = 'vacation', bool $remote = false): JsonResponse
    {
        $start = Carbon::parse($startStr);
        $end   = Carbon::parse($endStr);

        if ($start->gt($end)) [$start, $end] = [$end, $start];

        $years        = array_unique([$start->year, $end->year]);
        $holidayDates = Holiday::whereIn('year', $years)->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))->toArray();

        $birthdayDates = $this->birthdayStringsForRange($user, $start, $end);

        $current = $start->copy();
        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');

            if (!$current->isWeekend()
                && !in_array($dateStr, $holidayDates)
                && !in_array($dateStr, $birthdayDates)
            ) {
                Absence::updateOrCreate(
                    ['user_id' => $user->id, 'date' => $dateStr],
                    ['type' => $type, 'remote' => $remote]
                );
            }

            $current->addDay();
        }

        return response()->json(['status_days' => $this->yearStatusDays($user, $start->year)]);
    }

    private function deleteDay(User $user, string $date): JsonResponse
    {
        Absence::where('user_id', $user->id)
            ->where('date', $date)
            ->delete();

        $year = (int) substr($date, 0, 4);

        return response()->json(['status_days' => $this->yearStatusDays($user, $year)]);
    }

    private function deleteDateRange(User $user, string $startStr, string $endStr, string $type): JsonResponse
    {
        $start = Carbon::parse($startStr);
        $end   = Carbon::parse($endStr);

        if ($start->gt($end)) [$start, $end] = [$end, $start];

        Absence::where('user_id', $user->id)
            ->where('type', $type)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->delete();

        return response()->json(['status_days' => $this->yearStatusDays($user, $start->year)]);
    }

    private function yearStatusDays(User $user, int $year): array
    {
        return Absence::where('user_id', $user->id)
            ->whereIn('type', ['vacation', 'client', 'internal', 'undefined', 'training', 'absent'])
            ->whereYear('date', $year)
            ->get()
            ->map(fn($a) => [
                'date'   => $a->date->format('Y-m-d'),
                'type'   => $a->type,
                'remote' => (bool) $a->remote,
            ])
            ->values()
            ->toArray();
    }

    private function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        $first = mb_strtoupper(mb_substr($words[0], 0, 1));
        $last  = count($words) > 1 ? mb_strtoupper(mb_substr(end($words), 0, 1)) : '';
        return $first . $last;
    }

    private function birthdayStringsForRange(User $user, Carbon $start, Carbon $end): array
    {
        if (!$user->birthdate) return [];

        $dates = [];
        for ($y = $start->year; $y <= $end->year; $y++) {
            $bday    = $user->birthdate->copy()->setYear($y);
            $dateStr = $bday->format('Y-m-d');
            if ($bday->between($start, $end)) {
                $dates[] = $dateStr;
            }
        }
        return $dates;
    }
}
