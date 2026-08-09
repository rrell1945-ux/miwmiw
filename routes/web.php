<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::any('/debug-headers', function (\Illuminate\Http\Request $r) {
    return response()->json([
        'accept' => $r->headers->get('Accept'),
        'xrw' => $r->headers->get('X-Requested-With'),
        'expectsJson' => $r->expectsJson(),
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/notifications', [DashboardController::class, 'notifications'])->name('dashboard.notifications');

    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::patch('/settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::patch('/settings/reminders', [SettingsController::class, 'updateReminders'])->name('settings.reminders');

    Route::get('/periods/{date}', [PeriodController::class, 'show'])->name('periods.show');
    Route::post('/periods', [PeriodController::class, 'store'])->name('periods.store');
    Route::post('/periods/start', [PeriodController::class, 'start'])->name('periods.start');
    Route::post('/periods/{period}/extend', [PeriodController::class, 'extend'])->name('periods.extend');
    Route::post('/periods/{period}/finish', [PeriodController::class, 'finish'])->name('periods.finish');
    Route::put('/periods/{period}', [PeriodController::class, 'update'])->name('periods.update');
    Route::delete('/periods/{period}', [PeriodController::class, 'destroy'])->name('periods.destroy');

    Route::get('/backup/download', [BackupController::class, 'download'])->name('backup.download');
    Route::post('/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');

    Route::get('/export/pdf', [ExportController::class, 'pdf'])->name('export.pdf');
    Route::get('/export/excel', [ExportController::class, 'excel'])->name('export.excel');
});

require __DIR__.'/auth.php';


