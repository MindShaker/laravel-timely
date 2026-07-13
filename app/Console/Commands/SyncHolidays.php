<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncHolidays extends Command
{
    protected $signature = 'holidays:sync {year? : The year to sync (defaults to current year)}';
    protected $description = 'Fetch Portuguese public holidays from Nager.Date and upsert into the database';

    public function handle(): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);

        $response = Http::get("https://date.nager.at/api/v3/publicholidays/{$year}/PT");

        if ($response->failed()) {
            $this->error("Failed to fetch holidays from API (HTTP {$response->status()}).");
            return self::FAILURE;
        }

        $count = 0;

        foreach ($response->json() as $h) {
            $typesOk  = count(array_intersect($h['types'] ?? [], ['Public', 'Optional'])) > 0;
            $scopeOk  = ($h['global'] ?? false) || in_array('PT-11', $h['counties'] ?? []);

            if (!$typesOk || !$scopeOk) continue;

            Holiday::updateOrCreate(
                ['date' => $h['date']],
                ['name' => $h['localName'], 'year' => $year]
            );
            $count++;
        }

        // Almada municipal holiday — not in Nager.Date
        Holiday::updateOrCreate(
            ['date' => "{$year}-06-24"],
            ['name' => 'São João Baptista', 'year' => $year]
        );
        $count++;

        $this->info("Synced {$count} holidays for {$year}.");
        return self::SUCCESS;
    }
}
