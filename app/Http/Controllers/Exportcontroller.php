<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportController extends Controller
{
    private function buildSpreadsheet($logs): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'User');
        $sheet->setCellValue('B1', 'Date');
        $sheet->setCellValue('C1', 'Entry');
        $sheet->setCellValue('D1', 'Exit');
        $sheet->setCellValue('E1', 'Total Hours');
        $sheet->setCellValue('F1', 'Obs');

        $row = 2;
        foreach ($logs as $log) {
            $sheet->setCellValue('A' . $row, $log->user->name);
            $sheet->setCellValue('B' . $row, $log->data);
            $sheet->setCellValue('C' . $row, $log->entrada);
            $sheet->setCellValue('D' . $row, $log->saida);
            $sheet->setCellValue('E' . $row, $log->total_horas);
            $sheet->setCellValue('F' . $row, $log->obs);
            $row++;
        }

        return $spreadsheet;
    }

    private function sendFile(Spreadsheet $spreadsheet, string $format, string $filename): void
    {
        if ($format === 'xlsx') {
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        } else {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $writer->setDelimiter(';');
            $writer->setEnclosure('"');
            $writer->setLineEnding("\r\n");
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        }

        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->name != "") {
            $query->whereHas('user', fn($q) => $q->where('name', $request->name));
        }
        if ($request->month != "") {
            $query->where('data', 'like', $request->month . '%');
        }
        if ($request->time != "") {
            $query->whereDay('data', $request->time);
        }
        return $query;
    }

    public function export(Request $request)
    {
        $query = $this->applyFilters(Logs::with('User'), $request);
        $logs = $query->orderBy('data', 'DESC')->get();
        $this->sendFile($this->buildSpreadsheet($logs), $request->format, 'logs');
    }

    public function exportuserlog(Request $request)
    {
        $query = $this->applyFilters(
            Logs::with('User')->where('user_id', Auth::id()),
            $request
        );
        $logs = $query->orderBy('data', 'DESC')->get();
        $this->sendFile($this->buildSpreadsheet($logs), $request->format, 'Mylogs');
    }
}