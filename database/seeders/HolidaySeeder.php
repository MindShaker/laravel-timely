<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([now()->year, now()->year + 1] as $year) {
            Artisan::call('holidays:sync', ['year' => $year]);
            $this->command->info(trim(Artisan::output()));
        }
    }
}
