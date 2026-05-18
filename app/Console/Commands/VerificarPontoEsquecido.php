<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\logs; // Verifica se o teu model se chama Log
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LembretePontoMail;

class VerificarPontoEsquecido extends Command
{
    protected $signature = 'ponto:verificar-lembretes';
    protected $description = 'Envia lembretes de picagem de ponto em falta';

    public function handle()
    {
        $hoje = Carbon::today();

        // Se hoje for feriado ou fim de semana, o script não corre
        if ($hoje->isWeekend() || $this->isFeriado($hoje)) {
            return;
        }

        // Encontrar o último dia útil (andando para trás a partir de ontem)
        $ultimoDiaUtil = $hoje->copy()->subDay();
        while ($ultimoDiaUtil->isWeekend() || $this->isFeriado($ultimoDiaUtil)) {
            $ultimoDiaUtil->subDay();
        }

        // Buscar apenas utilizadores comuns
        $users = User::where('tipo', '=','user','and')->get();

        foreach ($users as $user) {
            // Verifica se existe log na data do último dia útil
            $temRegisto = logs::where('user_id', '=',$user->id,'and')
                             ->whereDate('created_at', $ultimoDiaUtil)
                             ->exists();

            if (!$temRegisto) {
                Mail::to($user->email)->send(new LembretePontoMail($ultimoDiaUtil->format('d/m/Y')));
                $this->info("Lembrete enviado para: " . $user->email);
            }
        }
    }

    private function isFeriado($date)
    {
        $ano = $date->year;
        $diaMes = $date->format('m-d');

        $feriadosFixos = [
            '01-01', '04-25', '05-01', '06-10', '08-15', 
            '10-05', '11-01', '12-01', '12-08', '12-25'
        ];

        if (in_array($diaMes, $feriadosFixos)) return true;

        // Feriados Móveis (Páscoa)
        $diasPascoa = easter_days($ano);
        $pascoa = Carbon::createFromDate($ano, 3, 21)->addDays($diasPascoa);
        
        $sextaFeiraSanta = $pascoa->copy()->subDays(2);
        $corpoDeDeus = $pascoa->copy()->addDays(60);

        return $date->isSameDay($sextaFeiraSanta) || $date->isSameDay($corpoDeDeus);
    }
}