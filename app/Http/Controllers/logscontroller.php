<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Logs;
use App\Models\User;
use App\Models\LogApproval;
use App\Models\AdminLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class logscontroller extends Controller
{
   private function calcTotal(string $entrada, string $saida, string $inicioAlmoco): string
    {
        $entry         = Carbon::parse($entrada);
        $exit          = Carbon::parse($saida);
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
     public function homepage(Request $request)
    {
        $id    = Auth::user()->id;
        $users = User::findOrFail($id);
        $data  = Carbon::now()->format('Y-m-d');

        $log = Logs::where('data','=', $data,'and')->whereIn('status', ['approved', 'pending'])->where('user_id', $id)->first();

        if (!$log) {
            return view("user/home");
        }

        if ($log->saida == "00:00:00") {
            return view("user/clockfinish", ['logs' => $log, 'users' => $users]);
        }

        return view("user/clockfinished", ['logs' => $log]);
    }

    public function userlogcreate(Request $request)
    {
        $id   = Auth::user()->id;
        $user = User::findOrFail($id);

        $endlunch = Carbon::parse($user->inicio_almoco)->addHour();

        $logs = Logs::create([
            'user_id'      => $id,
            'data'         => Carbon::now()->format('Y-m-d'),
            'entrada'      => Carbon::now()->format('H:i'),
            'final_almoço' => $endlunch,
            'saida'        => '00:00',
            'total_horas'  => '00:00',
            'obs'          => 'Manual Log',
            'created_by'   => $user->name,
            'updated_by'   => 'Not Updated',
        ]);

        return redirect(route('clockfinish', ['logs' => $logs]));
    }

    public function userlogup(Logs $logs)
    {
        return view("user/clockfinish", compact('logs'));
    }

    public function userlogupdate(Logs $logs)
    {
        $user  = User::findOrFail(Auth::user()->id);
        $saida = Carbon::now()->format('H:i');
        $total = $this->calcTotal($logs->entrada, $saida, $user->inicio_almoco);

        $logs->tipo_acao_custom = 'EXIT';
        $logs->update(['saida' => $saida, 'total_horas' => $total, 'updated_by' => $user->name]);

        return view("user/clockfinished", ['logs' => $logs]);
    }

    public function userlogs(Request $request)
    {
        $query = Logs::with('User')
            ->where('user_id', Auth::user()->id)
            ->where('status', 'approved');

        if ($request->month != "") $query->where('data', 'like', $request->month . '%');
        if ($request->time  != "") $query->whereDay('data', $request->time);

        $logs = $query->orderBy('data', 'DESC')->paginate(10);
        return view('user/logs', compact('logs'));
    }

    public function adminlogs(Request $request)
    {
        $users = User::all();
        $query = Logs::with('User')->where('status', 'approved');

        if ($request->name  != "") $query->whereHas('user', fn($q) => $q->where('name', $request->name));
        if ($request->month != "") $query->where('data', 'like', $request->month . '%');
        if ($request->time  != "") $query->whereDay('data', $request->time);

        $logs = $query->orderBy('data', 'DESC')->orderBy('entrada', 'DESC')->paginate(10);
        return view('admin/logs', compact('logs', 'users'));
    }

    public function adminLogsAudit(Request $request)
    {
        $users = User::all();
        $query = AdminLog::with(['autor', 'decisor']);

        if ($request->filled('name')) {
            $targetUser = User::where('name', '=', $request->name, 'and')->first();
            if ($targetUser) $query->where('dados_antigos->user_id', $targetUser->id);
        }

        if ($request->filled('month')) {
            $mesComTraco = $request->month;
            $mesComBarra = str_replace('-', '/', $request->month);
            $query->where(function ($q) use ($mesComTraco, $mesComBarra) {
                $q->where('dados_antigos->data', 'like', $mesComTraco . '%')
                  ->orWhere('dados_antigos->data', 'like', $mesComBarra . '%');
            });
        }

        $admin_logs = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin/admin_logs', compact('admin_logs', 'users'));
    }
    
    public function createlogview(
    )
    {
         return view("admin/createlogview", ['users' => User::all()]);
        
            
    }
    public function usercreatelogview()
    {
        
        return view("user/createlogview");
    }


    public function createlog(Request $request)
{
    $isAdmin = Auth::user()->tipo === 'admin';
    
    $isFromAdmin = $isAdmin && $request->filled('user_id');

    $rules = [
        'data'    => ['required', 'date'],
        'entrada' => ['required'],
        'saida'   => ['required'],
        'obs'     => ['required', 'string'],
    ];
    
    if ($isFromAdmin) {
        $rules['user_id'] = ['required', 'exists:users,id'];
    }

    $request->validate($rules);

    $userId = $isFromAdmin ? $request->user_id : Auth::id();
    $user   = User::findOrFail($userId);

     $logExists = Logs::where('user_id','=', $userId,'and')
        ->where('data', $request->data)
        ->whereIn('status', ['approved', 'pending'])
        ->exists();

    if ($logExists) {
        return redirect()->back()->withInput()
            ->with('message', 'A log record or pending request already exists for this day.');
    }

    $endlunch = Carbon::parse($user->inicio_almoco)->addHour();
    $total    = $this->calcTotal($request->entrada, $request->saida, $user->inicio_almoco);

    $log = Logs::create([
        'user_id'      => $userId,
        'data'         => $request->data,
        'entrada'      => $request->entrada,
        'final_almoço' => $endlunch->format('H:i'),
        'saida'        => $request->saida,
        'total_horas'  => $total,
        'obs'          => $request->obs,
        'created_by'   => Auth::user()->name,
        'updated_by'   => 'Not Updated',
        'status'       => $isAdmin ? 'approved' : 'pending',
    ]);

    if (!$isAdmin) {
        // Notifica o próprio user (só se tiver notificações ativas)
        if ($user->notifications) {
            Mail::to($user->email)->send(new \App\Mail\UserLogConfirmationMail($log));
        }
 
        $approveUrl = URL::temporarySignedRoute('admin.approve_new_log', now()->addHour(), ['id' => $log->id]);
        $rejectUrl  = URL::temporarySignedRoute('admin.reject_new_log',  now()->addHour(), ['id' => $log->id]);
 
        // Notifica apenas os admins com notificações ativas
        foreach (User::where('tipo', '=', 'admin', 'and')->where('notifications', 1)->get() as $admin) {
            Mail::to($admin->email)->send(new \App\Mail\NewLogRequestMail($log, $user, $approveUrl, $rejectUrl));
        }
    }

    if ($isAdmin) {
        if ($isFromAdmin) {
            return redirect()->route('adminlogs')->with('success', 'Log created and approved successfully!');
        }
       return redirect()->route('userlogs')->with('success', 'Log created and approved successfully!');
    }

   
    return redirect()->route('userlogs')->with('success', 'Your log request has been submitted for approval!');
}

   public function looklog($logs, Request $request)
    {
        $logs = Logs::findOrFail($logs);
        $this->authorizeLogAccess($logs);

        return request()->is('admin/*')
            ? view("admin/looklog", compact('logs'))
            : view("user/looklog",  compact('logs'));
    }

    public function editlog($logs)
    {
        $logs  = Logs::findOrFail($logs);
        $this->authorizeLogAccess($logs);
        $users = User::all();

        return request()->is('admin/*')
            ? view("admin/editlog", compact('logs', 'users'))
            : view("user/editlog",  compact('logs', 'users'));
    }

    public function updatelog(Logs $logs, Request $request)
    {
        $this->authorizeLogAccess($logs);
 
        $data = $request->validate([
            'data'    => ['required'],
            'obs'     => ['required'],
            'saida'   => ['required'],
            'entrada' => ['required'],
        ]);
 
        $user  = User::findOrFail($request->user_id);
        $taken = Logs::with('User')
            ->where('user_id', $user->id)
            ->where('data', $data['data'])
            ->whereIn('status', ['approved', 'pending'])
            ->where('id', '!=', $logs->id)
            ->exists();
 
        if ($taken) {
            return redirect()->back()->withInput()->with('message', 'A log already exists for that date.');
        }
 
        $endlunch = Carbon::parse($user->inicio_almoco)->addHour()->format('H:i');
        $total    = $data['saida'] !== '00:00'
            ? $this->calcTotal($data['entrada'], $data['saida'], $user->inicio_almoco)
            : '00:00:00';
 
        $dadosPreparados = array_merge($data, [
            'total_horas'  => $total,
            'final_almoço' => $endlunch,
            'updated_by'   => Auth::user()->name,
        ]);
 
        if (Auth::user()->tipo === 'admin') {
            $logs->acao_personalizada = 'EDIT';
            $logs->update($dadosPreparados);
            return redirect()->route('adminlogs')->with('message', 'Log updated successfully!');
        }
 
        $approval = LogApproval::create([
            'log_id'      => $logs->id,
            'user_id'     => Auth::user()->id,
            'dados_novos' => (object) $dadosPreparados,
            'status'      => 'pending',
        ]);
 
        // Notifica apenas admins com notificações ativas
        foreach (User::where('tipo', '=', 'admin', 'and')->where('notifications', 1)->get() as $admin) {
            Mail::to($admin->email)->send(
                new \App\Mail\LogEditRequestMail(Auth::user(), $logs, $dadosPreparados, $approval->id)
            );
        }
 
        return redirect()->route('userlogs')
            ->with('success', 'Your log modification request has been successfully submitted for approval.');
    }

    public function deletelog($logs, Request $request)
    {
        $logs = Logs::findOrFail($logs);
        $this->authorizeLogAccess($logs);
        $logs->delete();

        return request()->is('admin/*')
            ? redirect()->route('adminlogs')->with('message', 'The log has been successfully removed')
            : redirect()->route('userlogs')->with('message', 'The log has been successfully removed');
    }

    private function authorizeLogAccess(Logs $log): void
    {
        if ($log->user_id !== Auth::user()->id && Auth::user()->tipo !== 'admin') {
            abort(403, 'Unauthorized access');
        }
    }
}