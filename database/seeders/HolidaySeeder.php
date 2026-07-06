<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = now()->year;

        foreach ([$currentYear, $currentYear + 1] as $year) {
            foreach ($this->holidaysForYear($year) as $date => $name) {
                Holiday::updateOrCreate(['date' => $date], ['name' => $name, 'year' => $year]);
            }
        }
    }

    public static function holidaysForYear(int $year): array
    {
        $easter        = Carbon::create($year, 3, 21)->addDays(easter_days($year));
        $goodFriday    = $easter->copy()->subDays(2)->format('Y-m-d');
        $corpusChristi = $easter->copy()->addDays(60)->format('Y-m-d');

        return [
            "{$year}-01-01" => 'Ano Novo',
            $goodFriday     => 'Sexta-feira Santa',
            "{$year}-04-25" => 'Dia da Liberdade',
            "{$year}-05-01" => 'Dia do Trabalhador',
            "{$year}-06-10" => 'Dia de Portugal',
            $corpusChristi  => 'Corpo de Deus',
            "{$year}-08-15" => 'Assunção de Nossa Senhora',
            "{$year}-10-05" => 'Implantação da República',
            "{$year}-11-01" => 'Dia de Todos os Santos',
            "{$year}-12-01" => 'Restauração da Independência',
            "{$year}-12-08" => 'Imaculada Conceição',
            "{$year}-12-25" => 'Natal',
        ];
    }
}
