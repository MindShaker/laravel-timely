<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Http\Response;

class CalendarFeedController extends Controller
{
    public function feed(string $token): Response
    {
        $user = User::where('calendar_token', $token)->firstOrFail();

        $absences = Absence::where('user_id', $user->id)
            ->where('type', 'vacation')
            ->orderBy('date')
            ->pluck('date');

        $spans = $this->mergeSpans($absences->all());

        $dtstamp = gmdate('Ymd\THis\Z');
        $lines   = [];

        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:2.0';
        $lines[] = 'PRODID:-//Mindshaker//Timely//PT';
        $lines[] = 'CALSCALE:GREGORIAN';
        $lines[] = 'METHOD:PUBLISH';
        $lines[] = 'X-WR-CALNAME:Férias — ' . $user->name;
        $lines[] = 'X-WR-TIMEZONE:Europe/Lisbon';
        $lines[] = 'REFRESH-INTERVAL;VALUE=DURATION:PT12H';
        $lines[] = 'X-PUBLISHED-TTL:PT12H';

        foreach ($spans as [$start, $end]) {
            $dtstart = $start->format('Ymd');
            $dtend   = $end->copy()->addDay()->format('Ymd');
            $uid     = 'timely-' . $user->id . '-' . $dtstart . '@mindshaker.com';

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . $dtstamp;
            $lines[] = 'DTSTART;VALUE=DATE:' . $dtstart;
            $lines[] = 'DTEND;VALUE=DATE:' . $dtend;
            $lines[] = 'SUMMARY:Férias';
            $lines[] = 'END:VEVENT';
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
            if ($gap <= 3) {
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
