<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\AdminLog;
use App\Models\LogApproval;
use App\Models\User;
use App\Mail\NewLogStatusMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class LogApprovalController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    private function actionResultView(bool $success, string $message, string $status, string $by, ?string $at)
    {
        return view('admin.action_result', [
            'success' => $success,
            'message' => $message,
            'details' => ['status' => $status, 'processed_by' => $by, 'processed_at' => $at],
        ]);
    }

    private function getProcessor(int $logId): ?AdminLog
    {
        return AdminLog::where('log_id', '=', $logId, 'and')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    private function alreadyProcessedView(string $status, int $logId): \Illuminate\View\View
    {
        $processor = $this->getProcessor($logId);
        $who = $processor ? ($processor->decisor?->name ?? $processor->admin_id) : 'Unknown';
        return $this->actionResultView(
            $status === 'approved',
            'This request has already been processed.',
            $status, $who,
            $processor?->created_at?->toDateTimeString()
        );
    }

    /**
     * Checks if the signed link is expired (signature invalid OR 1h time window passed).
     */
    private function isLinkExpired(Request $request, Logs $log): bool
    {
        $signatureInvalid = method_exists($request, 'hasValidSignature')
            && !$request->hasValidSignature();

        $timeExpired = $log->created_at->copy()->addHour()->isPast();

        return $signatureInvalid || $timeExpired;
    }

    /**
     * Called when a new-log signed link has expired.
     * Deletes the pending log and records it as EXPIRED in admin_logs.
     */
    private function handleExpiredNewLog(Logs $log): \Illuminate\View\View
    {
        // Only act if still pending — another admin may have already expired it
        if ($log->status === 'pending') {
            $dadosAntigos = $log->getAttributes();

            AdminLog::create([
                'log_id'        => $log->id,
                'user_id'       => $log->user_id,
                'admin_id'      => null,
                'acao'          => 'EXPIRED',
                'dados_antigos' => $dadosAntigos,
                'dados_novos'   => ['status' => 'expired'],
            ]);

            $log->withoutEvents(fn() => $log->forceDelete());
        }

        return $this->actionResultView(
            false,
            'This link has expired. The pending log request has been automatically cancelled.',
            'expired',
            'System',
            now()->toDateTimeString()
        );
    }

    // ── New Log Requests (signed URL) ─────────────────────────────────────────

    public function approveNewLog(Request $request, $id)
    {
        if (!Auth::check()) {
            session(['url.intended' => URL::full()]);
            return redirect()->route('login');
        }

        if (Auth::user()->tipo !== 'admin') {
            return redirect()->route('userlogs')->with('error', 'Dont have permission.');
        }

        $log = Logs::findOrFail($id);

        if ($this->isLinkExpired($request, $log)) {
            return $this->handleExpiredNewLog($log);
        }

        if ($log->status !== 'pending') {
            return $this->alreadyProcessedView($log->status, $log->id);
        }

        $dadosAntigos = $log->getAttributes();
        $log->withoutEvents(fn() => tap($log)->forceFill(['status' => 'approved'])->save());

        AdminLog::create([
            'log_id'        => $log->id,
            'user_id'       => $log->user_id,
            'admin_id'      => Auth::id(),
            'acao'          => 'CREATION_ACCEPTED',
            'dados_antigos' => $dadosAntigos,
            'dados_novos'   => ['status' => 'approved'],
        ]);

        $log->load('user');
        if ($log->user->notifications) {
            Mail::to($log->user->email)->send(new NewLogStatusMail($log, 'approved'));
        }

        $processor = $this->getProcessor($log->id);
        return $this->actionResultView(
            true,
            'New log request approved successfully.',
            'approved',
            $processor?->decisor?->name ?? Auth::user()->name,
            $processor?->created_at?->toDateTimeString()
        );
    }

    public function rejectNewLog(Request $request, $id)
    {
        if (!Auth::check()) {
            session(['url.intended' => URL::full()]);
            return redirect()->route('login');
        }

        if (Auth::user()->tipo !== 'admin') {
            return redirect()->route('userlogs')->with('error', 'Dont have permission.');
        }

        $log = Logs::findOrFail($id);

        if ($this->isLinkExpired($request, $log)) {
            return $this->handleExpiredNewLog($log);
        }

        if ($log->status !== 'pending') {
            return $this->alreadyProcessedView($log->status, $log->id);
        }

        $dadosAntigos = $log->getAttributes();
        $log->withoutEvents(fn() => tap($log)->forceFill(['status' => 'rejected'])->save());

        AdminLog::create([
            'log_id'        => $log->id,
            'user_id'       => $log->user_id,
            'admin_id'      => Auth::id(),
            'acao'          => 'CREATION_REJECTED',
            'dados_antigos' => $dadosAntigos,
            'dados_novos'   => ['status' => 'rejected'],
        ]);

        $log->load('user');
        if ($log->user->notifications) {
            Mail::to($log->user->email)->send(new NewLogStatusMail($log, 'rejected'));
        }

        $processor = $this->getProcessor($log->id);
        return $this->actionResultView(
            false,
            'New log request rejected.',
            'rejected',
            $processor?->decisor?->name ?? Auth::user()->name,
            $processor?->created_at?->toDateTimeString()
        );
    }

    // ── Edit Requests ─────────────────────────────────────────────────────────

    public function approveLog($id)
    {
        if (Auth::user()->tipo !== 'admin') {
            return redirect()->route('userlogs')->with('error', 'You do not have permission to approve logs.');
        }

        $approval = LogApproval::findOrFail($id);

        if ($approval->status !== 'pending') {
            return $this->alreadyProcessedView($approval->status, $approval->log_id);
        }

        if ($approval->created_at->copy()->addMinutes(60)->isPast()) {
            return $this->handleExpiredApproval($approval);
        }

        $logOriginal  = Logs::findOrFail($approval->log_id);
        $dadosAntigos = $logOriginal->getAttributes();
        $dadosNovos   = is_array($approval->dados_novos)
            ? $approval->dados_novos
            : json_decode($approval->dados_novos, true);

        $logOriginal->withoutEvents(fn() => $logOriginal->update($dadosNovos));
        $approval->update(['status' => 'approved', 'admin_id' => Auth::id()]);

        AdminLog::create([
            'log_id'        => $logOriginal->id,
            'user_id'       => $approval->user_id,
            'admin_id'      => Auth::id(),
            'acao'          => 'APPROVED',
            'dados_antigos' => $dadosAntigos,
            'dados_novos'   => $dadosNovos,
        ]);

        $this->notifyUser($approval->user_id, $logOriginal, 'approved', $dadosNovos);

        return $this->actionResultView(true, 'Log edit request approved and user notified!',
            'approved', Auth::user()->name, now()->toDateTimeString());
    }

    public function rejectLog($id)
    {
        if (Auth::user()->tipo !== 'admin') {
            return redirect()->route('userlogs')->with('error', 'You do not have permission to reject logs.');
        }

        $approval = LogApproval::findOrFail($id);

        if ($approval->status !== 'pending') {
            return $this->alreadyProcessedView($approval->status, $approval->log_id);
        }

        if ($approval->created_at->copy()->addMinutes(60)->isPast()) {
            return $this->handleExpiredApproval($approval);
        }

        $logOriginal = Logs::findOrFail($approval->log_id);
        $approval->update(['status' => 'rejected', 'admin_id' => Auth::id()]);

        AdminLog::create([
            'log_id'        => $approval->log_id,
            'user_id'       => $approval->user_id,
            'admin_id'      => Auth::id(),
            'acao'          => 'REJECTED',
            'dados_antigos' => $logOriginal->getAttributes(),
            'dados_novos'   => ['status' => 'rejected'],
        ]);

        $this->notifyUser($approval->user_id, $logOriginal, 'rejected', []);

        return $this->actionResultView(false, 'Log edit request rejected and user notified.',
            'rejected', Auth::user()->name, now()->toDateTimeString());
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Edit request expired — keep original log intact, just mark approval as expired.
     */
    private function handleExpiredApproval(LogApproval $approval): \Illuminate\View\View
    {
        if ($approval->status === 'pending') {
            $logOriginal = Logs::findOrFail($approval->log_id);

            $approval->update(['status' => 'expired', 'admin_id' => null]);

            AdminLog::create([
                'log_id'        => $approval->log_id,
                'user_id'       => $approval->user_id,
                'admin_id'      => null,
                'acao'          => 'EXPIRED',
                'dados_antigos' => $logOriginal?->getAttributes() ?? [],
                'dados_novos'   => ['status' => 'expired'],
            ]);
        }

        return $this->actionResultView(
            false,
            'This link has expired. The edit request has been automatically cancelled.',
            'expired',
            'System',
            now()->toDateTimeString()
        );
    }

    private function notifyUser(int $userId, Logs $log, string $status, array $dados): void
    {
        $user = User::findOrFail($userId);
        if ($user && $user->notifications) {
            Mail::to($user->email)->send(new \App\Mail\LogStatusUpdatedMail($user, $log, $status, $dados));
        }
    }
}