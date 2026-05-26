<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class usercontroller extends Controller
{
    private const FILL_HEADER  = 'FEF2CB';
    private const FILL_ADMIN   = 'D6E4F0';
    private const COMPANY      = 'Empresa: Mindshaker - Serviços Informáticos, Lda.';

    // ── User list ─────────────────────────────────────────────────────────────

    public function userlist(Request $request)
    {
        if ($request->input('name') == "") {
            $users = User::query()->orderBy('name', 'asc')->paginate(10);
        } else {
            $users = User::query()
                ->where("name", "LIKE", "%" . $request->input('name') . "%")
                ->orderBy('name', 'asc')
                ->paginate(10)
                ->withQueryString();
        }
        return view('admin/users', compact('users'));
    }

    // ── Export users ──────────────────────────────────────────────────────────

    public function exportusers(Request $request)
    {
        $users  = User::all();
        $format = $request->format;

        if ($format === 'csv') {
            $this->streamUsersCsv($users);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Utilizadores');

        // ── Row 1: metadata ───────────────────────────────────────────────────
        $sheet->setCellValue('A1', 'Lista de Utilizadores');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->setCellValue('F1', self::COMPANY);
        $sheet->getStyle('F1')->applyFromArray([
            'font'      => ['size' => 11, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Row 2: column headers ─────────────────────────────────────────────
        $headers = [
            'A' => 'Nome',
            'B' => 'Email',
            'C' => 'Início Almoço',
            'D' => 'Tipo',
            'E' => 'Notificações',
            'F' => 'Criado em',
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
        $row = 3;
        foreach ($users as $user) {
            $isAdmin = $user->tipo === 'admin';
            $fillRgb = $isAdmin ? self::FILL_ADMIN : null;

            $sheet->setCellValue("A{$row}", $user->name);
            $sheet->setCellValue("B{$row}", $user->email);
            $sheet->setCellValue("C{$row}", $user->inicio_almoco ?? '-');
            $sheet->setCellValue("D{$row}", $isAdmin ? 'Administrador' : 'Utilizador');
            $sheet->setCellValue("E{$row}", $user->notifications ? 'Sim' : 'Não');
            $sheet->setCellValue("F{$row}", $user->created_at?->format('d/m/Y'));

            $rowStyle = [
                'font'      => ['size' => 11, 'name' => 'Calibri'],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ];
            if ($fillRgb) {
                $rowStyle['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $fillRgb]];
            }
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($rowStyle);

            // Center align columns C, D, E, F
            foreach (['C', 'D', 'E', 'F'] as $col) {
                $sheet->getStyle("{$col}{$row}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $sheet->getRowDimension($row)->setRowHeight(19.5);
            $row++;
        }

        // ── Total row ─────────────────────────────────────────────────────────
        $totalRow = $row;
        $sheet->setCellValue("A{$totalRow}", 'Total: ' . $users->count() . ' utilizador(es)');
        $sheet->getStyle("A{$totalRow}")->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($totalRow)->setRowHeight(19.5);

        // ── Column widths ─────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(25.0);
        $sheet->getColumnDimension('B')->setWidth(35.0);
        $sheet->getColumnDimension('C')->setWidth(14.0);
        $sheet->getColumnDimension('D')->setWidth(16.0);
        $sheet->getColumnDimension('E')->setWidth(14.0);
        $sheet->getColumnDimension('F')->setWidth(14.0);

        // ── Send ──────────────────────────────────────────────────────────────
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Mindshaker - Utilizadores.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    private function streamUsersCsv($users): never
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach (
            [
                'A' => 'Nome',
                'B' => 'Email',
                'C' => 'Início Almoço',
                'D' => 'Tipo',
                'E' => 'Notificações',
                'F' => 'Criado em'
            ] as $col => $label
        ) {
            $sheet->setCellValue("{$col}1", $label);
        }
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue("A{$row}", $user->name);
            $sheet->setCellValue("B{$row}", $user->email);
            $sheet->setCellValue("C{$row}", $user->inicio_almoco ?? '-');
            $sheet->setCellValue("D{$row}", $user->tipo === 'admin' ? 'Administrador' : 'Utilizador');
            $sheet->setCellValue("E{$row}", $user->notifications ? 'Sim' : 'Não');
            $sheet->setCellValue("F{$row}", $user->created_at?->format('d/m/Y'));
            $row++;
        }
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
        $writer->setDelimiter(';')->setEnclosure('"')->setLineEnding("\r\n");
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Mindshaker - Utilizadores.csv"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // ── Create user ───────────────────────────────────────────────────────────

    public function createuserview()
    {
        return view("admin/createuserview");
    }

    public function createuser(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', Rules\Password::defaults()],
            'type'     => ['required', 'string', 'max:255'],
            'lunch'    => ['required', 'max:255'],
        ]);

        try {
            User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'tipo'          => $request->type,
                'inicio_almoco' => $request->lunch,
            ]);
            return redirect(route('userlist', absolute: false))->with('success', 'User created successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while creating the user. Please try again.')->withInput();
        }
    }

    public function changeusertype(Request $request, User $user)
    {
        // Validação de segurança para garantir que só entram os 3 tipos permitidos
        $request->validate([
            'tipo' => ['required', 'in:user,worker,admin'],
        ]);

        // Atualiza com o valor que veio do formulário
        $user->update([
            'tipo' => $request->tipo
        ]);

        return redirect()->back()->with('success', 'Role updated successfully!');
    }

    // ── Biometrics ────────────────────────────────────────────────────────────

    public function enroll(Request $request, $id)
    {
        $user = User::findOrFail($id);

        try {
            $mqtt = $this->makeMqttClient('enroll_web_client_' . $id);
            $mqtt->publish('Enroll/UserID', (string) $id, 0, false);
            $mqtt->publish('Enroll/Nome', $user->name, 0, false);
            $mqtt->disconnect();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', "Command sended! The sensor will start the enrollment process for {$user->name}.");
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error communicating with the Broker: ' . $e->getMessage());
        }
    }

    public function deleteFinger($id)
    {
        $user = User::findOrFail($id);

        try {
            $mqtt = $this->makeMqttClient('delete_web_client_' . $id);
            $mqtt->publish('Delete/UserID', (string) $id, 0, false);
            $mqtt->disconnect();
            return back()->with('success', "Command sended! The sensor will delete the biometric data for {$user->name}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error communicating with the Broker MQTT: ' . $e->getMessage());
        }
    }

    public function checkFingerStatus($id)
    {
        $user = User::findOrFail($id);
        return response()->json(['finger' => $user->finger]);
    }

    public function receberStatusEnroll(Request $request)
    {
        $userId = $request->input('user_id');
        $status = $request->input('status');

        if ($userId === null || $status === null) {
            return response()->json(['error' => 'Incomplete data'], 400);
        }

        $user = User::findOrFail($userId);

        if ($status == 1) {
            $user->update(['finger' => 1]);
            return response()->json(['success' => true, 'message' => 'Biometric data activated successfully!']);
        }
        return response()->json(['success' => false, 'message' => 'Error reading the finger']);
    }

    public function receberStatusDeleteFinger(Request $request)
    {
        $userId = $request->input('user_id');
        $status = $request->input('status');

        if ($userId === null || $status === null) {
            return response()->json(['error' => 'Incomplete data'], 400);
        }

        $user = User::findOrFail($userId);

        if ($status == 1) {
            $user->update(['finger' => 0]);
            return response()->json(['sucesso' => true, 'mensagem' => 'Delete biometric data successfully!']);
        }
        return response()->json(['sucesso' => false, 'mensagem' => 'Error deleting the finger']);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function makeMqttClient(string $clientId): \PhpMqtt\Client\MqttClient
    {
        $host     = config('mqtt.host');
        $port     = (int) config('mqtt.port');
        $username = config('mqtt.username');
        $password = config('mqtt.password');

        if (!$host || !$port) {
            throw new \RuntimeException('MQTT is not configured. Please check config/mqtt.php and your .env file (MQTT_HOST, MQTT_PORT).');
        }

        $settings = (new \PhpMqtt\Client\ConnectionSettings)
            ->setUseTls(true)
            ->setTlsVerifyPeer(false)
            ->setUsername($username)
            ->setPassword($password);

        $client = new \PhpMqtt\Client\MqttClient($host, $port, $clientId);
        $client->connect($settings, true);
        return $client;
    }
}
