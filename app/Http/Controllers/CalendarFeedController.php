<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Http\Response;

class CalendarFeedController extends Controller
{
    public function feed(string $token): Response
    {
        // Validate token — 404 on bad/missing token
        User::where('calendar_token', $token)->firstOrFail();

        // Load all users' vacation absences, grouped by user
        $absencesByUser = Absence::with('user')
            ->where('type', 'vacation')
            ->orderBy('date')
            ->get()
            ->groupBy('user_id');

        $dtstamp = gmdate('Ymd\THis\Z');
        $lines   = [];

        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:2.0';
        $lines[] = 'PRODID:-//Mindshaker//Timely//PT';
        $lines[] = 'CALSCALE:GREGORIAN';
        $lines[] = 'METHOD:PUBLISH';
        $lines[] = 'X-WR-CALNAME:Férias — Mindshaker';
        $lines[] = 'X-WR-TIMEZONE:Europe/Lisbon';
        $lines[] = 'REFRESH-INTERVAL;VALUE=DURATION:PT12H';
        $lines[] = 'X-PUBLISHED-TTL:PT12H';

        foreach ($absencesByUser as $userId => $absences) {
            $person = $absences->first()->user;
            $spans  = $this->mergeSpans($absences->pluck('date')->all());

            foreach ($spans as [$start, $end]) {
                $dtstart = $start->format('Ymd');
                $dtend   = $end->copy()->addDay()->format('Ymd');
                $uid     = 'timely-' . $userId . '-' . $dtstart . '@mindshaker.com';

                $lines[] = 'BEGIN:VEVENT';
                $lines[] = 'UID:' . $uid;
                $lines[] = 'DTSTAMP:' . $dtstamp;
                $lines[] = 'DTSTART;VALUE=DATE:' . $dtstart;
                $lines[] = 'DTEND;VALUE=DATE:' . $dtend;
                $lines[] = 'SUMMARY:Férias — ' . $person->name;
                $lines[] = 'END:VEVENT';
            }
        }

        $lines[] = 'END:VCALENDAR';

        $ics = implode("\r\n", $lines) . "\r\n";

        return response($ics, 200, [
            'Content-Type'  => 'text/calendar; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function mergeSpans(array $dates): array
    {
        if (empty($dates)) return [];

        $spans = [];
        $start = $dates[0];
        $prev  = $dates[0];

        for ($i = 1; $i < count($dates); $i++) {
            $gap = $prev->diffInDays($dates[$i]);
            if ($gap === 1) {
                $prev = $dates[$i];
            } else {
                $spans[] = [$start, $prev];
                $start   = $dates[$i];
                $prev    = $dates[$i];
            }
        }
        $spans[] = [$start, $prev];

        return $spans;
    }
}
