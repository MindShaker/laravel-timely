<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportController extends Controller
{
    private const MONTHS_PT = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    private const COMPANY      = 'Empresa: Mindshaker - Serviços Informáticos, Lda.';
    private const FILL_HEADER  = 'FEF2CB';
    private const FILL_WEEKEND = 'BFBFBF';
    private const FILL_HOLIDAY = 'D8D8D8';

    // ── Public endpoints ──────────────────────────────────────────────────────

    public function export(Request $request)
    {
        if ($request->format === 'csv') $this->streamCsv($request);

        // Check for incomplete logs unless the user already confirmed
        if (!$request->boolean('force')) {
            $incomplete = $this->findIncompleteLogs($request);
            if (!empty($incomplete)) {
                return view('admin.export_confirm', [
                    'incomplete'   => $incomplete,
                    'isAdmin'      => true,
                    'exportRoute'  => route('export'),
                    'params'       => $request->only(['name', 'month', 'time', 'format']),
                ]);
            }
        }

        $hasName  = $request->filled('name');
        $hasMonth = $request->filled('month');
        $year     = $hasMonth ? (int) substr($request->month, 0, 4) : now()->year;

        if ($hasName && $hasMonth) {
            $user        = User::where('name', '=', $request->name, 'and')->firstOrFail();
            $month       = (int) substr($request->month, 5, 2);
            $spreadsheet = $this->makeSpreadsheet();
            $sheet       = $spreadsheet->createSheet()->setTitle(self::MONTHS_PT[$month]);
            $this->buildMonthSheet($sheet, $user, $month, $year, $this->fetchLogs($user->id, $request->month));
            $this->sendXlsx($spreadsheet, "Mindshaker - {$user->name} - " . self::MONTHS_PT[$month] . " {$year}");
        }

        if ($hasMonth && !$hasName) {
            $month       = (int) substr($request->month, 5, 2);
            $spreadsheet = $this->makeSpreadsheet();
            foreach (User::all() as $user) {
                $sheet = $spreadsheet->createSheet()->setTitle($this->safeSheetName($user->name));
                $this->buildMonthSheet($sheet, $user, $month, $year, $this->fetchLogs($user->id, $request->month));
            }
            $this->sendXlsx($spreadsheet, "Mindshaker - " . self::MONTHS_PT[$month] . " {$year}");
        }

        if ($hasName && !$hasMonth) {
            $user        = User::where('name', '=', $request->name, 'and')->firstOrFail();
            $spreadsheet = $this->makeSpreadsheet();
            $sheetsAdded = 0;
            foreach (self::MONTHS_PT as $month => $monthName) {
                $logs = $this->fetchLogs($user->id, sprintf('%d-%02d', $year, $month));
                if ($logs->isEmpty()) continue;
                $sheet = $spreadsheet->createSheet()->setTitle($monthName);
                $this->buildMonthSheet($sheet, $user, $month, $year, $logs);
                $sheetsAdded++;
            }
            if ($sheetsAdded === 0) $spreadsheet->createSheet()->setTitle('Sem Registos');
            $this->sendXlsx($spreadsheet, "Mindshaker - {$user->name} - {$year}");
        }

         if (!$hasName && !$hasMonth) {
            if (!$request->filled('year')) {
                // Procura todos os anos únicos que existem na tabela de logs
                $availableYears = Logs::selectRaw('YEAR(data) as year',[])
                    ->distinct()
                    ->orderBy('year', 'desc')
                    ->pluck('year');

                return view('admin.export_year_selector', [
                    'exportRoute'    => route('export'),
                    'params'         => $request->only(['format', 'force']),
                    'availableYears' => $availableYears,
                ]);
            }

            $this->streamAllUsersZip((int) $request->year);
        }
    }

    public function exportuserlog(Request $request)
    {
        $user     = User::findOrFail(Auth::id());
        $hasMonth = $request->filled('month');
        $year     = $hasMonth ? (int) substr($request->month, 0, 4) : now()->year;

        if ($request->format === 'csv') $this->streamCsv($request, $user->id);

        // Check for incomplete logs unless already confirmed
        if (!$request->boolean('force')) {
            $incomplete = $this->findIncompleteLogsForUser($user->id, $request);
            if (!empty($incomplete)) {
                return view('admin.export_confirm', [
                    'incomplete'  => [$user->name => $incomplete],
                    'isAdmin'     => false,
                    'exportRoute' => route('exportuserlog'),
                    'params'      => $request->only(['month', 'time', 'format']),
                ]);
            }
        }

        $spreadsheet = $this->makeSpreadsheet();

        if ($hasMonth) {
            $month    = (int) substr($request->month, 5, 2);
            $sheet    = $spreadsheet->createSheet()->setTitle(self::MONTHS_PT[$month]);
            $this->buildMonthSheet($sheet, $user, $month, $year, $this->fetchLogs($user->id, $request->month));
            $filename = "Mindshaker - {$user->name} - " . self::MONTHS_PT[$month] . " {$year}";
        } else {
            $sheetsAdded = 0;
            foreach (self::MONTHS_PT as $month => $monthName) {
                $logs = $this->fetchLogs($user->id, sprintf('%d-%02d', $year, $month));
                if ($logs->isEmpty()) continue;
                $sheet = $spreadsheet->createSheet()->setTitle($monthName);
                $this->buildMonthSheet($sheet, $user, $month, $year, $logs);
                $sheetsAdded++;
            }
            if ($sheetsAdded === 0) $spreadsheet->createSheet()->setTitle('Sem Registos');
            $filename = "Mindshaker - {$user->name} - {$year}";
        }

        $this->sendXlsx($spreadsheet, $filename);
    }

    // ── Incomplete log detection ──────────────────────────────────────────────

    /**
     * Returns ['Person Name' => ['2025-05-01', '2025-05-03', ...], ...]
     * Only for logs matching the current export filters.
     */
    private function findIncompleteLogs(Request $request): array
    {
        $query = Logs::with('user')
            ->where('status', 'approved')
            ->where(fn($q) => $q->whereNull('saida')
                ->orWhere('saida', '00:00')
                ->orWhere('saida', '00:00:00'));

        if ($request->filled('name')) {
            $query->whereHas('user', fn($q) => $q->where('name', $request->name));
        }
        if ($request->filled('month')) {
            $query->where('data', 'like', $request->month . '%');
        }

        $incomplete = [];
        foreach ($query->orderBy('data')->get() as $log) {
            $incomplete[$log->user->name][] = $log->data;
        }
        return $incomplete;
    }

    /**
     * Same but scoped to a single user (for exportuserlog).
     * Returns a flat array of date strings.
     */
    private function findIncompleteLogsForUser(int $userId, Request $request): array
    {
        $query = Logs::where('user_id', '=', $userId, 'and')
            ->where('status', 'approved')
            ->where(fn($q) => $q->whereNull('saida')
                ->orWhere('saida', '00:00')
                ->orWhere('saida', '00:00:00'));

        if ($request->filled('month')) {
            $query->where('data', 'like', $request->month . '%');
        }

        return $query->orderBy('data')->pluck('data')->toArray();
    }

    // ── Core sheet builder ────────────────────────────────────────────────────
private function buildMonthSheet(Worksheet $sheet, User $user, int $month, int $year, $logs): void
    {
        $monthName    = self::MONTHS_PT[$month];
        $daysInMonth  = Carbon::create($year, $month, 1)->daysInMonth;
        
        // Fallbacks estáticos caso o log não tenha registo de almoço
        $inicioAlmocoPadrao = $user->inicio_almoco ?? '12:30';
        $fimAlmocoPadrao    = Carbon::parse($inicioAlmocoPadrao)->addHour()->format('H:i');
        
        $holidays     = $this->getPortugueseHolidays($year);
        $lastDataRow  = $daysInMonth + 2;
        $totalRow     = $daysInMonth + 3;
        $avgRow       = $daysInMonth + 4;

        // ── Row 1: header ─────────────────────────────────────────────────────
        $headerData = [
            'A1' => ['Trabalhador:', true,  Alignment::HORIZONTAL_LEFT],
            'B1' => [$user->name,     false, Alignment::HORIZONTAL_LEFT],
            'C1' => ['Mês:',         true,  Alignment::HORIZONTAL_RIGHT],
            'D1' => [$monthName,     false, Alignment::HORIZONTAL_LEFT],
            'E1' => ['Ano:',         true,  Alignment::HORIZONTAL_RIGHT],
            'F1' => [$year,          false, Alignment::HORIZONTAL_LEFT],
            'G1' => [self::COMPANY,  false, Alignment::HORIZONTAL_LEFT],
        ];
        foreach ($headerData as $cell => [$value, $bold, $align]) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->applyFromArray([
                'font'      => ['bold' => $bold, 'size' => 11, 'name' => 'Calibri'],
                'alignment' => ['horizontal' => $align, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }
        $sheet->getRowDimension(1)->setRowHeight(19.5);

        // ── Row 2: column headers ─────────────────────────────────────────────
        $headers = [
            'A' => 'Data',
            'B' => 'Hora de Entrada',
            'C' => 'Início Pausa',
            'D' => 'Fim Pausa',
            'E' => 'Hora de Saída',
            'F' => 'Total Horas',
            'G' => 'Observações',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}2", $label);
            $sheet->getStyle("{$col}2")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 11, 'name' => 'Calibri'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::FILL_HEADER]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }
        $sheet->getRowDimension(2)->setRowHeight(19.5);

        // ── Data rows ─────────────────────────────────────────────────────────
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date      = Carbon::create($year, $month, $day);
            $dateStr   = $date->format('Y-m-d');
            $row       = $day + 2;
            $isWeekend = $date->isWeekend();
            $isHoliday = in_array($dateStr, $holidays);

            $fillRgb = match (true) {
                $isWeekend => self::FILL_WEEKEND,
                $isHoliday => self::FILL_HOLIDAY,
                default    => null,
            };

            $sheet->setCellValue("A{$row}", ExcelDate::PHPToExcel($date->copy()->setTime(12, 0, 0)->getTimestamp()));
            $sheet->getStyle("A{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $sheet->getStyle("A{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $log = $logs->get($dateStr);

            if ($log && !$isWeekend && !$isHoliday) {
                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'font'      => ['size' => 11, 'name' => 'Calibri'],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $this->setTimeCell($sheet, "B{$row}", $log->entrada);

                // 1. Procura o fim de almoço usando a propriedade correta: final_almoço
                $temFimAlmocoValido = !empty($log->final_almoço) && !in_array(trim($log->final_almoço), ['00:00', '00:00:00']);
                $fimAlmoco = $temFimAlmocoValido ? trim($log->final_almoço) : $fimAlmocoPadrao;

                // 2. Calcula o início do almoço dinamicamente (-1 hora)
                $inicioAlmoco = Carbon::parse($fimAlmoco)->subHour()->format('H:i');

                // 3. Grava os valores nas colunas C (Início Pausa) e D (Fim Pausa)
                $this->setTimeCell($sheet, "C{$row}", $inicioAlmoco);
                $this->setTimeCell($sheet, "D{$row}", $fimAlmoco);

                $exitOk = $log->saida && !in_array(trim($log->saida), ['00:00', '00:00:00']);
                if ($exitOk) $this->setTimeCell($sheet, "E{$row}", $log->saida);
                if ($log->obs) $sheet->setCellValue("G{$row}", $log->obs);
            }

            if ($isHoliday && $log?->obs) $sheet->setCellValue("G{$row}", $log->obs);

            $sheet->setCellValue("F{$row}", "=IFERROR(IF(OR(B{$row}=0,E{$row}=0),0,IF(AND(B{$row}<D{$row},E{$row}>C{$row}),(E{$row}-B{$row})-(MIN(E{$row},D{$row})-MAX(B{$row},C{$row})),E{$row}-B{$row})),0)");
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('[h]:mm');
            $sheet->getStyle("F{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $rowStyle = [
                'font'      => ['size' => 11, 'name' => 'Calibri'],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ];
            if ($fillRgb) $rowStyle['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillRgb]];
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($rowStyle);
            $sheet->getRowDimension($row)->setRowHeight(19.5);
        }

        $sheet->getStyle("B3:E{$lastDataRow}")->getNumberFormat()->setFormatCode('hh:mm');

        // ── Total row ─────────────────────────────────────────────────────────
        $sheet->setCellValue("E{$totalRow}", 'Total mensal');
        $sheet->getStyle("E{$totalRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->setCellValue("F{$totalRow}", "=SUM(F3:F{$lastDataRow})");
        $sheet->getStyle("F{$totalRow}")->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::FILL_HEADER]],
            'font'      => ['size' => 11, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle("F{$totalRow}")->getNumberFormat()->setFormatCode('[h]:mm');
        $sheet->getRowDimension($totalRow)->setRowHeight(19.5);

        // ── Average row ───────────────────────────────────────────────────────
        $sheet->setCellValue("E{$avgRow}", 'Média diária');
        $sheet->getStyle("E{$avgRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->setCellValue("F{$avgRow}", "=IFERROR(F{$totalRow}/COUNTIF(F3:F{$lastDataRow}, \">0\"), 0)");
        $sheet->getStyle("F{$avgRow}")->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::FILL_HEADER]],
            'font'      => ['size' => 11, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle("F{$avgRow}")->getNumberFormat()->setFormatCode('[h]:mm');
        $sheet->getRowDimension($avgRow)->setRowHeight(19.5);

        // ── Column widths ─────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(13.57);
        $sheet->getColumnDimension('B')->setWidth(16.0);
        $sheet->getColumnDimension('C')->setWidth(14.0);
        $sheet->getColumnDimension('D')->setWidth(14.0);
        $sheet->getColumnDimension('E')->setWidth(14.0);
        $sheet->getColumnDimension('F')->setWidth(13.0);
        $sheet->getColumnDimension('G')->setWidth(50.0);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeSpreadsheet(): Spreadsheet
    {
        $s = new Spreadsheet();
        $s->removeSheetByIndex(0);
        return $s;
    }

    private function fetchLogs(int $userId, string $monthPrefix)
    {
        return Logs::where('user_id', '=', $userId, 'and')
            ->where('status', 'approved')
            ->where('data', 'like', $monthPrefix . '%')
            ->get()->keyBy('data');
    }

    private function setTimeCell(Worksheet $sheet, string $cell, string $time): void
    {
        $time = trim($time);
        if (!$time || in_array($time, ['00:00', '00:00:00'])) return;
        [$h, $m] = explode(':', $time);
        $sheet->setCellValue($cell, ((int)$h * 60 + (int)$m) / 1440);
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('hh:mm');
        $sheet->getStyle($cell)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }

    private function sendXlsx(Spreadsheet $spreadsheet, string $filename): never
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    private function streamAllUsersZip(int $year): never
    {
        $tmpFiles = [];
        foreach (User::all() as $user) {
            $spreadsheet = $this->makeSpreadsheet();
            $sheetsAdded = 0;

            foreach (self::MONTHS_PT as $month => $monthName) {
                $logs = $this->fetchLogs($user->id, sprintf('%d-%02d', $year, $month));
                if ($logs->isEmpty()) continue;
                $sheet = $spreadsheet->createSheet()->setTitle($monthName);
                $this->buildMonthSheet($sheet, $user, $month, $year, $logs);
                $sheetsAdded++;
            }

            // Skip users with no logs entirely — no file generated for them
            if ($sheetsAdded === 0) continue;

            $tmp = tempnam(sys_get_temp_dir(), 'ms_') . '.xlsx';
            (new Xlsx($spreadsheet))->save($tmp);
            $tmpFiles["Mindshaker - {$user->name} - {$year}.xlsx"] = $tmp;
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'ms_zip_') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($tmpFiles as $name => $path) $zip->addFile($path, $name);
        $zip->close();

        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=\"Mindshaker_Logs_{$year}.zip\"");
        header('Content-Length: ' . filesize($zipPath));
        header('Cache-Control: max-age=0');
        readfile($zipPath);

        unlink($zipPath);
        foreach ($tmpFiles as $path) @unlink($path);
        exit;
    }

    private function streamCsv(Request $request, ?int $userId = null): never
    {
        $query = Logs::with('user')->where('status', 'approved');
        if ($userId)                   $query->where('user_id', $userId);
        if ($request->filled('name'))  $query->whereHas('user', fn($q) => $q->where('name', $request->name));
        if ($request->filled('month')) $query->where('data', 'like', $request->month . '%');

        $logs = $query->orderBy('data', 'DESC')->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach (
            [
                'A' => 'Trabalhador',
                'B' => 'Data',
                'C' => 'Hora de Entrada',
                'D' => 'Hora de Saída',
                'E' => 'Total Horas',
                'F' => 'Observações'
            ] as $col => $label
        ) {
            $sheet->setCellValue("{$col}1", $label);
        }
        $row = 2;
        foreach ($logs as $log) {
            $sheet->setCellValue("A{$row}", $log->user->name);
            $sheet->setCellValue("B{$row}", $log->data);
            $sheet->setCellValue("C{$row}", $log->entrada);
            $sheet->setCellValue("D{$row}", $log->saida);
            $sheet->setCellValue("E{$row}", $log->total_horas);
            $sheet->setCellValue("F{$row}", $log->obs);
            $row++;
        }
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
        $writer->setDelimiter(';')->setEnclosure('"')->setLineEnding("\r\n");
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . ($userId ? 'Mylogs' : 'logs') . '.csv"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    private function safeSheetName(string $name): string
    {
        return substr(preg_replace('/[\/\\\?\*\[\]:]/', '', $name), 0, 31);
    }

    private function getPortugueseHolidays(int $year): array
    {
        $fixed = [
            "{$year}-01-01",
            "{$year}-04-25",
            "{$year}-05-01",
            "{$year}-06-10",
            "{$year}-08-15",
            "{$year}-10-05",
            "{$year}-11-01",
            "{$year}-12-01",
            "{$year}-12-08",
            "{$year}-12-25",
        ];
        $easter        = Carbon::create($year, 3, 21)->addDays(easter_days($year));
        $goodFriday    = $easter->copy()->subDays(2)->format('Y-m-d');
        $corpusChristi = $easter->copy()->addDays(60)->format('Y-m-d');
        return array_merge($fixed, [$goodFriday, $corpusChristi]);
    }
}
