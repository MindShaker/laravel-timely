<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    public function index()
    {
        $users = User::orderBy('name')->get(['id', 'name']);
        return \Inertia\Inertia::render('Admin/Export/Index', compact('users'));
    }

    public function download(Request $request)
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'from'       => 'required|date_format:Y-m',
            'to'         => 'required|date_format:Y-m',
        ]);

        abort_if($request->to < $request->from, 422, 'O mês de fim deve ser igual ou posterior ao de início.');

        $users = User::whereIn('id', $request->user_ids)->orderBy('name')->get();
        $from  = Carbon::parse($request->from . '-01');
        $to    = Carbon::parse($request->to . '-01');

        // All months in range
        $months = [];
        for ($cur = $from->copy(); $cur->lte($to); $cur->addMonth()) {
            $months[] = ['year' => $cur->year, 'month' => $cur->month];
        }

        // Filename range label
        $singleMonth = $from->format('Y-m') === $to->format('Y-m');
        $sameYear    = $from->year === $to->year;
        if ($singleMonth) {
            $rangeLabel = self::MONTHS_PT[$from->month] . ' ' . $from->year;
        } elseif ($sameYear) {
            $rangeLabel = self::MONTHS_PT[$from->month] . ' a ' . self::MONTHS_PT[$to->month] . ' ' . $to->year;
        } else {
            $rangeLabel = self::MONTHS_PT[$from->month] . ' ' . $from->year
                . ' a ' . self::MONTHS_PT[$to->month] . ' ' . $to->year;
        }

        $tmpDir = sys_get_temp_dir() . '/export_' . uniqid('', true);
        mkdir($tmpDir, 0700, true);

        $files = [];
        foreach ($users as $user) {
            $spreadsheet = $this->makeSpreadsheet();
            foreach ($months as ['year' => $year, 'month' => $month]) {
                $title = count($months) > 1
                    ? self::MONTHS_PT[$month] . ' ' . $year
                    : self::MONTHS_PT[$month];
                $sheet = $spreadsheet->createSheet()->setTitle($title);
                $this->buildAttendanceSheet($sheet, $user, $month, $year);
            }

            $filename = "Mindshaker - {$user->name} - {$rangeLabel}.xlsx";
            $tmpPath  = $tmpDir . '/' . $filename;
            (new Xlsx($spreadsheet))->save($tmpPath);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            $files[] = ['path' => $tmpPath, 'name' => $filename];
        }

        $zipName = "Mindshaker - Registo de Ponto - {$rangeLabel}.zip";
        $zipPath = $tmpDir . '/export.zip';

        $zip = new \ZipArchive();
        abort_if($zip->open($zipPath, \ZipArchive::CREATE) !== true, 500, 'Não foi possível criar o ficheiro ZIP.');
        foreach ($files as ['path' => $path, 'name' => $name]) {
            $zip->addFile($path, $name);
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment;filename="' . $zipName . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Cache-Control: max-age=0');
        readfile($zipPath);

        foreach ($files as ['path' => $path]) {
            @unlink($path);
        }
        @unlink($zipPath);
        @rmdir($tmpDir);
        exit;
    }

    // ── Sheet builder ─────────────────────────────────────────────────────────

    private function buildAttendanceSheet(Worksheet $sheet, User $user, int $month, int $year): void
    {
        $monthName   = self::MONTHS_PT[$month];
        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
        $holidays    = $this->getPortugueseHolidayDates($year);
        $lastDataRow = $daysInMonth + 2;
        $totalRow    = $daysInMonth + 3;
        $avgRow      = $daysInMonth + 4;

        $entrada      = $user->hora_entrada    ?? '09:00';
        $inicioAlmoco = $user->inicio_almoco   ?? '13:00';
        $fimAlmoco    = Carbon::parse($inicioAlmoco)->addHour()->format('H:i');
        $saida        = $user->hora_saida      ?? '18:00';

        // Absences this month (keyed by date string)
        $absences = Absence::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn($a) => $a->date->format('Y-m-d'));

        // Birthday in this month
        $birthdayStr = null;
        if ($user->birthdate) {
            $bday = $user->birthdate->copy()->setYear($year);
            if ((int) $bday->format('m') === $month) {
                $birthdayStr = $bday->format('Y-m-d');
            }
        }

        // ── Row 1: header ─────────────────────────────────────────────────────
        $headerData = [
            'A1' => ['Trabalhador:', true,  Alignment::HORIZONTAL_LEFT],
            'B1' => [$user->name,    false, Alignment::HORIZONTAL_LEFT],
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
            $isHoliday = isset($holidays[$dateStr]);
            $absence   = $absences->get($dateStr);
            $isBirthday = $dateStr === $birthdayStr;

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

            if (!$isWeekend && !$isHoliday) {
                $leaveEmpty = $isBirthday || ($absence && in_array($absence->type, ['vacation', 'absent']));

                if ($leaveEmpty) {
                    $obs = match (true) {
                        $isBirthday                   => 'Aniversário',
                        $absence->type === 'vacation' => 'Férias',
                        default                       => 'Ausente',
                    };
                    $sheet->setCellValue("G{$row}", $obs);
                } else {
                    $this->setTimeCell($sheet, "B{$row}", $entrada);
                    $this->setTimeCell($sheet, "C{$row}", $inicioAlmoco);
                    $this->setTimeCell($sheet, "D{$row}", $fimAlmoco);
                    $this->setTimeCell($sheet, "E{$row}", $saida);

                }
            }

            if ($isHoliday) $sheet->setCellValue("G{$row}", $holidays[$dateStr]);

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

    private function getPortugueseHolidayDates(int $year): array
    {
        return \App\Models\Holiday::whereYear('date', $year)
            ->get()
            ->mapWithKeys(fn($h) => [$h->date->format('Y-m-d') => $h->name])
            ->toArray();
    }
}
