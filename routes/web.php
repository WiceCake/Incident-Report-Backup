<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Middleware\AuthenticationMiddleware;
use App\Http\Controllers\SecurityEventsController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // Security Events

    Route::get('/security-events', [SecurityEventsController::class, 'index'])->name('report.security');
    Route::post('/security-events/create', [SecurityEventsController::class, 'create'])->name('report.security.create');
    Route::get('/security-events/{id}', [SecurityEventsController::class, 'show'])->name('report.security.view');


    // Incident Reports
    Route::get('/incident-reports', [IncidentReportController::class, 'index'])->name('report.incident');
    Route::get('/incident-reports/{id}', [IncidentReportController::class, 'show'])->name('report.incident.view');
    Route::post('/incident-reports/complete', [IncidentReportController::class, 'complete'])->name('report.incident.complete');

    // Logs

    Route::get('/user-logs', function () {
        return view('pages.logs.user-logs');
    })->name('logs.user');

    Route::get('/honeypot-logs', function () {
        return view('pages.logs.honeypot-logs');
    })->name('logs.honeypot');
});



// Manage Reports



// Route::get('/incident-reports/{id}/edit', function(){
//     return view('pages.reports.incident-reports.edit-report');
// })->name('report.incident.edit');


// Route::get('/login', [AuthController::class, 'login']);
