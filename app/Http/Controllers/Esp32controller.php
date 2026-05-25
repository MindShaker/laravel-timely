<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\AdminLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Esp32Controller extends Controller
{
    public function receberPontoDoEsp32(Request $request)
    {
        try {
            $userId = $request->user_id;
            $user   = User::findOrFail($userId);
            $hoje   = Carbon::now()->format('Y-m-d');
            $agora  = Carbon::now()->format('H:i');

            $logHoje = Logs::where('user_id', '=', $userId, 'and')
                ->where('data', $hoje)
                ->whereIn('status', ['approved', 'pending'])
                ->first();

            if (!$logHoje) {
                $endlunch = Carbon::parse($user->inicio_almoco)->addHour();
                Logs::withoutEvents(function () use ($userId, $hoje, $agora, $endlunch) {
                    Logs::create([
                        'user_id'      => $userId,
                        'data'         => $hoje,
                        'entrada'      => $agora,
                        'final_almoço' => $endlunch->format('H:i'),
                        'saida'        => '00:00',
                        'total_horas'  => '00:00',
                        'obs'          => 'Automatic Log',
                        'created_by'   => 'ESP32 System',
                        'updated_by'   => 'Not Updated',
                    ]);
                });
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Entry registered for ' . $user->name,
                    'hora'    => $agora,
                ]);
            }

            // No exit yet → register exit
            if (in_array($logHoje->saida, ['00:00', '00:00:00'])) {
                $total = $this->calcTotal($logHoje->entrada, $agora, $user->inicio_almoco);
                $logHoje->withoutEvents(function () use ($logHoje, $agora, $total) {
                    $logHoje->update([
                        'saida'       => $agora,
                        'total_horas' => $total,
                        'updated_by'  => 'ESP32 System',
                    ]);
                });
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Saída registada para ' . $user->name,
                    'total'   => $total,
                ]);
            }

            // Already has entry + exit → after-hours attempt
            AdminLog::create([
                'log_id'        => $logHoje->id,
                'user_id'       => $userId,
                'acao'          => 'AFTER HOURS',
                'dados_antigos' => $logHoje->toArray(),
                'dados_novos'   => [
                    'saida'     => $agora,
                    'obs'       => 'After Hours Attempt',
                    'ip_origem' => $request->ip(),
                ],
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Log for today already has an exit time. After hours attempt recorded and admin notified.',
            ], 400);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function calcTotal(string $entrada, string $agora, string $inicioAlmoco): string
    {
        $entry         = Carbon::parse($entrada);
        $exit          = Carbon::parse($agora);
        $inicio_almoco = Carbon::parse($inicioAlmoco);
        $fim_almoco    = $inicio_almoco->copy()->addHour();

        $totalMinutos = $entry->diffInMinutes($exit);

        if ($entry->lessThan($fim_almoco) && $exit->greaterThan($inicio_almoco)) {
            $inicio_sobreposicao = $entry->greaterThan($inicio_almoco) ? $entry : $inicio_almoco;
            $fim_sobreposicao    = $exit->lessThan($fim_almoco) ? $exit : $fim_almoco;
            $totalMinutos       -= $inicio_sobreposicao->diffInMinutes($fim_sobreposicao);
        }

        $totalMinutos = max(0, $totalMinutos);
        return sprintf('%02d:%02d', floor($totalMinutos / 60), $totalMinutos % 60);
    }
}