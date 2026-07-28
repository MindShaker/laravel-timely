<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function show(int $year = null, int $month = null)
    {
        $year  = $year  ?? now()->year;
        $month = $month ?? now()->month;

        $user    = Auth::user();
        $props   = $this->buildCalendarProps($user, $year, $month, true);
        $props['feedUrl'] = route('calendar.feed', ['token' => $user->getOrCreateCalendarToken()]);

        return Inertia::render('Calendar/Show', $props);
    }

    public function adminShow(User $user, int $year = null, int $month = null)
    {
        $year  = $year  ?? now()->year;
        $month = $month ?? now()->month;

        return Inertia::render('Calendar/Admin', $this->buildCalendarProps($user, $year, $month, false));
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

    public function week(Request $request, int $year = null, int $week = null)
    {
        $currentUser = Auth::user();
        $year  = $year  ?? now()->isoWeekYear;
        $week  = $week  ?? now()->isoWeek;
        $span  = max(1, min(2, (int) $request->get('span', 1)));

        $monday = Carbon::now()->setISODate($year, $week)->startOfDay();
        $end    = $monday->copy()->addDays($span * 7 - 1);

        $days = array_map(
            fn($i) => $monday->copy()->addDays($i)->format('Y-m-d'),
            range(0, $span * 7 - 1)
        );

        $holidays = Holiday::whereBetween('date', [$monday->toDateString(), $end->toDateString()])
            ->get()
            ->mapWithKeys(fn($h) => [$h->date->format('Y-m-d') => $h->name])
            ->toArray();

        $absenceGrid = [];
        Absence::whereBetween('date', [$monday->toDateString(), $end->toDateString()])
            ->whereIn('type', ['vacation', 'client', 'internal', 'undefined', 'training', 'absent'])
            ->get()
            ->each(function ($absence) use (&$absenceGrid) {
                $absenceGrid[$absence->user_id][$absence->date->format('Y-m-d')] = [
                    'type'   => $absence->type,
                    'remote' => (bool) $absence->remote,
                ];
            });

        $calendarYears = array_unique([$monday->year, $end->year]);
        $birthdayGrid  = [];
        User::whereNotNull('birthdate')->get(['id', 'name', 'birthdate'])
            ->each(function ($u) use (&$birthdayGrid, $days, $calendarYears) {
                foreach ($calendarYears as $y) {
                    $bday = $u->birthdate->copy()->setYear($y)->format('Y-m-d');
                    if (in_array($bday, $days)) {
                        $birthdayGrid[$u->id][$bday] = true;
                        break;
                    }
                }
            });

        $users = User::orderBy('name')->get(['id', 'name'])->toArray();

        $currentIdx = array_search($currentUser->id, array_column($users, 'id'));
        if ($currentIdx !== false) {
            $me = array_splice($users, $currentIdx, 1)[0];
            array_unshift($users, $me);
        }

        $prevWeek = $monday->copy()->subWeeks($span);
        $nextWeek = $monday->copy()->addWeeks($span);

        return Inertia::render('Calendar/Week', [
            'year'         => $year,
            'week'         => $week,
            'span'         => $span,
            'days'         => $days,
            'holidays'     => $holidays,
            'absenceGrid'  => $absenceGrid,
            'birthdayGrid' => $birthdayGrid,
            'users'        => $users,
            'currentUser'  => ['id' => $currentUser->id, 'name' => $currentUser->name],
            'today'        => now()->format('Y-m-d'),
            'mondayYear'   => $monday->year,
            'mondayMonth'  => $monday->month,
            'prevWeek'     => ['year' => $prevWeek->isoWeekYear, 'week' => $prevWeek->isoWeek],
            'nextWeek'     => ['year' => $nextWeek->isoWeekYear, 'week' => $nextWeek->isoWeek],
        ]);
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

        $monthsData = [];
        foreach ($months as $m => $monthData) {
            $typesData = [];
            foreach ($monthData['types'] as $type => $typeData) {
                $typesData[$type] = [
                    'days'        => $typeData['days'],
                    'peopleCount' => count($typeData['people']),
                ];
            }
            $monthsData[$m] = ['total' => $monthData['total'], 'types' => $typesData];
        }

        $yearTotalsData = [];
        foreach ($yearTotals as $type => $info) {
            $yearTotalsData[$type] = [
                'days'        => $info['days'],
                'peopleCount' => count($info['people']),
            ];
        }

        return Inertia::render('Calendar/Overview', [
            'year'       => $year,
            'months'     => $monthsData,
            'monthNames' => $monthNames,
            'yearTotals' => $yearTotalsData,
            'today'      => now()->format('Y-m-d'),
        ]);
    }

    public function vacationOverview(int $year = null)
    {
        $year = $year ?? now()->year;

        $users = User::orderBy('name')->get(['id', 'name']);

        $absences = Absence::where('type', 'vacation')
            ->whereYear('date', $year)
            ->get(['user_id', 'date']);

        // Build data[userId][month] = count
        $data = [];
        foreach ($absences as $a) {
            $data[$a->user_id][$a->date->month] = ($data[$a->user_id][$a->date->month] ?? 0) + 1;
        }

        $monthNames = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar',  4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul',   8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
        ];

        $rows = $users->map(function ($user) use ($data) {
            $months = [];
            $total  = 0;
            for ($m = 1; $m <= 12; $m++) {
                $count     = $data[$user->id][$m] ?? 0;
                $months[$m] = $count;
                $total     += $count;
            }
            return ['id' => $user->id, 'name' => $user->name, 'months' => $months, 'total' => $total];
        });

        return Inertia::render('Admin/Vacation/Index', [
            'year'       => $year,
            'rows'       => $rows,
            'monthNames' => $monthNames,
            'allowance'  => 22,
            'prevYear'   => $year - 1,
            'nextYear'   => $year + 1,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildCalendarProps(User $user, int $year, int $month, bool $showAll = false): array
    {
        $firstDay    = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startOffset = $firstDay->dayOfWeek; // Sun=0…Sat=6

        $yearStatusDays = $this->yearStatusDays($user, $year);

        $holidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->mapWithKeys(fn($h) => [$h->date->format('Y-m-d') => $h->name])
            ->toArray();

        $birthdayDates = [];
        if ($user->birthdate) {
            $bday = $user->birthdate->copy()->setYear($year);
            if ((int) $bday->format('m') === $month) {
                $birthdayDates[] = $bday->format('Y-m-d');
            }
        }

        $totalWorkdays = 0;
        $holidayCount  = count($holidays);
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $dow     = (int) date('w', mktime(0, 0, 0, $month, $d, $year));
            if ($dow !== 0 && $dow !== 6 && !isset($holidays[$dateStr]) && !in_array($dateStr, $birthdayDates)) {
                $totalWorkdays++;
            }
        }

        $vacationCount = count(array_filter($yearStatusDays, fn($s) =>
            $s['type'] === 'vacation'
            && str_starts_with($s['date'], sprintf('%04d-%02d', $year, $month))
        ));

        $yearVacationCount = Absence::where('user_id', $user->id)
            ->where('type', 'vacation')
            ->whereYear('date', $year)
            ->count();

        $vacationAllowance = 22;

        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        $monthNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        $props = [
            'user'               => ['id' => $user->id, 'name' => $user->name],
            'year'               => $year,
            'month'              => $month,
            'monthName'          => $monthNames[$month],
            'daysInMonth'        => $daysInMonth,
            'startOffset'        => $startOffset,
            'totalWorkdays'      => $totalWorkdays,
            'yearStatusDays'     => $yearStatusDays,
            'holidays'           => $holidays,
            'birthdayDates'      => $birthdayDates,
            'prevMonth'          => ['year' => $prevMonth->year, 'month' => $prevMonth->month],
            'nextMonth'          => ['year' => $nextMonth->year, 'month' => $nextMonth->month],
            'yearVacationCount'  => $yearVacationCount,
            'vacationAllowance'  => $vacationAllowance,
            'holidayCount'       => $holidayCount,
            'workedDays'         => max(0, $totalWorkdays - $vacationCount),
        ];

        if ($showAll) {
            $othersStatus    = [];
            $othersBirthdays = [];

            Absence::with('user')
                ->whereIn('type', ['vacation', 'client', 'internal', 'undefined', 'training', 'absent'])
                ->where('user_id', '!=', $user->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get()
                ->each(function ($absence) use (&$othersStatus) {
                    $othersStatus[$absence->date->format('Y-m-d')][] = [
                        'user_id'  => $absence->user_id,
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

            $props['teamMembers']     = User::where('id', '!=', $user->id)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
                ->values()
                ->toArray();
            $props['othersStatus']    = $othersStatus;
            $props['othersBirthdays'] = $othersBirthdays;
        }

        return $props;
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
