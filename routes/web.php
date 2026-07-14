<?php

use App\Http\Controllers\AdminHolidayController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('calendar')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    // Profile (Breeze default)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Calendar — user's own
    Route::get('/calendar',                    [CalendarController::class, 'show'])->name('calendar');
    Route::get('/calendar/{year}/overview',    [CalendarController::class, 'yearOverview'])->name('calendar.overview')->where('year', '\d{4}');
    Route::get('/calendar/week/{year?}/{week?}', [CalendarController::class, 'week'])->name('calendar.week')->where(['year' => '\d{4}', 'week' => '\d{1,2}']);
    Route::get('/calendar/{year}/{month}',     [CalendarController::class, 'show'])->name('calendar.month')->where(['year' => '\d{4}', 'month' => '\d{1,2}']);
    Route::post('/calendar/range',             [CalendarController::class, 'markRange'])->name('calendar.range');
    Route::delete('/calendar/range',           [CalendarController::class, 'removeRange'])->name('calendar.removeRange');
    Route::delete('/calendar/{date}',          [CalendarController::class, 'remove'])->name('calendar.remove')
        ->where('date', '\d{4}-\d{2}-\d{2}');

    // Admin routes
    Route::prefix('admin')->middleware('is_admin')->group(function () {

        // Users
        Route::get('/users',              [UserController::class, 'index'])->name('admin.users');
        Route::get('/users/create',       [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/users',             [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}/edit',  [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}',       [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}',    [UserController::class, 'destroy'])->name('admin.users.destroy');

        // Admin calendar (view/edit any user's calendar)
        Route::get('/calendar/{user}',                    [CalendarController::class, 'adminShow'])->name('admin.calendar');
        Route::get('/calendar/{user}/{year}/{month}',     [CalendarController::class, 'adminShow'])->name('admin.calendar.month');
        Route::post('/calendar/{user}/range',             [CalendarController::class, 'adminMarkRange'])->name('admin.calendar.range');
        Route::delete('/calendar/{user}/range',          [CalendarController::class, 'adminRemoveRange'])->name('admin.calendar.removeRange');
        Route::delete('/calendar/{user}/{date}',          [CalendarController::class, 'adminRemove'])->name('admin.calendar.remove')
            ->where('date', '\d{4}-\d{2}-\d{2}');

        // Holidays
        Route::get('/holidays/{year?}',      [AdminHolidayController::class, 'index'])->name('admin.holidays')->where('year', '\d{4}');
        Route::post('/holidays/sync/{year}', [AdminHolidayController::class, 'sync'])->name('admin.holidays.sync')->where('year', '\d{4}');
        Route::post('/holidays',             [AdminHolidayController::class, 'store'])->name('admin.holidays.store');
        Route::patch('/holidays/{holiday}',  [AdminHolidayController::class, 'update'])->name('admin.holidays.update');
        Route::delete('/holidays/{holiday}', [AdminHolidayController::class, 'destroy'])->name('admin.holidays.destroy');

        // Export
        Route::get('/export',          [ExportController::class, 'index'])->name('admin.export');
        Route::get('/export/download', [ExportController::class, 'download'])->name('admin.export.download');
    });
});

require __DIR__ . '/auth.php';
