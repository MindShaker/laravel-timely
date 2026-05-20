<?php
 
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\usercontroller;
use App\Http\Controllers\logscontroller;
use App\Http\Controllers\Exportcontroller;
use App\Http\Controllers\LogApprovalcontroller;
use App\Http\Controllers\Esp32controller;
 
 
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
});
 
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');
 

Route::post('/esp32/ponto', [Esp32Controller::class, 'receberPontoDoEsp32']);
Route::post('/esp32/enroll-status', [usercontroller::class, 'receberStatusEnroll']);
Route::post('/esp32/delete-finger-status', [usercontroller::class, 'receberStatusDeleteFinger']);
 
Route::middleware('auth')->group(function () {
 
    
    Route::get('/dashboard', fn() => view('user/home'))->middleware(['verified'])->name('dashboard');
 
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
 
    
    Route::prefix('user')->group(function () {
        Route::get('/logs',                         [logscontroller::class, 'userlogs'])->name('userlogs');
        Route::get('/home',                         [logscontroller::class, 'homepage'])->name('home');
        Route::post('/home/create',                 [logscontroller::class, 'userlogcreate'])->name('logcreate');
        Route::get('/clockfinish/{logs}',           [logscontroller::class, 'userlogup'])->name('clockfinish');
        Route::put('/clockfinishupdate/{logs}',     [logscontroller::class, 'userlogupdate'])->name('clockfinishupdate');
        Route::get('/looklog/{logs}',               [logscontroller::class, 'looklog'])->name('userlooklog');
        Route::get('/editlog/{logs}',               [logscontroller::class, 'editlog'])->name('usereditlog');
        Route::put('/editlog/{logs}/update',        [logscontroller::class, 'updatelog'])->name('updateuserlog');
        Route::delete('/delete/{logs}',             [logscontroller::class, 'deletelog'])->name('deleteuserlog');
    });
 
    Route::prefix('admin')->group(function () {
 
        Route::get('/logs',                         [logscontroller::class, 'adminlogs'])->name('adminlogs');
        Route::get('/createlogview',                [logscontroller::class, 'createlogview'])->name('createlogview');
        Route::post('/createlog',                   [logscontroller::class, 'createlog'])->name('createlog');
        Route::delete('/delete/{logs}',             [logscontroller::class, 'deletelog'])->name('deletelog');
        Route::get('/looklog/{logs}',               [logscontroller::class, 'looklog']);
        Route::get('/editlog/{logs}',               [logscontroller::class, 'editlog']);
        Route::put('/editlog/{logs}/update',        [logscontroller::class, 'updatelog'])->name('updatelog');
        Route::get('/admin-logs',                   [logscontroller::class, 'adminLogsAudit'])->name('admin.adminlogs');
 
        Route::get('/export',                       [ExportController::class, 'export'])->name('export');
        Route::get('/export/logs',                  [ExportController::class, 'exportuserlog'])->name('exportuserlog');
        Route::get('/export/users',                 [usercontroller::class, 'exportusers'])->name('exportusers');
 
        Route::get('/approve-log/{id}',             [LogApprovalController::class, 'approveLog'])->name('admin.approve_log');
        Route::get('/reject-log/{id}',              [LogApprovalController::class, 'rejectLog'])->name('admin.reject_log');
 
        Route::get('/approve-new-log/{id}',         [LogApprovalController::class, 'approveNewLog'])->name('admin.approve_new_log');
        Route::get('/reject-new-log/{id}',          [LogApprovalController::class, 'rejectNewLog'])->name('admin.reject_new_log');
 
         Route::get('/users',                        [usercontroller::class, 'userlist'])->name('userlist');
        Route::post('/users/{id}/delete-finger',    [usercontroller::class, 'deleteFinger'])->name('users.delete_finger');
        Route::get('/createuserview',               [usercontroller::class, 'createuserview'])->name('createuserview');
        Route::post('/usercreate',                  [usercontroller::class, 'createuser'])->name('createuser');
        Route::put('/change/{user}',                [usercontroller::class, 'changeusertype'])->name('changeusertype');
        Route::get('/users/{id}/finger-status',     [usercontroller::class, 'checkFingerStatus'])->name('users.finger_status');
    });
 
    Route::post('/users/{id}/enroll', [usercontroller::class, 'enroll'])->name('users.enroll');
});
 
 
require __DIR__.'/auth.php';