<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\AuthenticationMiddleware;
use App\Http\Controllers\SecurityEventsController;

Route::get('/', function(){
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function(){
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

});


// Security Events

Route::get('/security-events', [SecurityEventsController::class, 'index'])->name('report.security');

Route::post('/security-events/create', [SecurityEventsController::class, 'create'])->name('report.security.create');

Route::get('/security-events/{id}', [SecurityEventsController::class, 'show'])->name('report.security.view');

// Manage Reports

Route::get('/incident-reports', function(){
    return view('pages.reports.incident-reports');
})->name('report.incident');

// Route::get('/incident-reports/{id}/edit', function(){
//     return view('pages.reports.incident-reports.edit-report');
// })->name('report.incident.edit');

Route::get('/incident-reports/{id}', function(){
    return view('pages.reports.incident-reports.view-report');
})->name('report.incident.view');

// All Reports

Route::get('/all-reports', function(){
    return view('pages.reports.all-reports');
})->name('report.all');

Route::get('/user-logs', function(){
    return view('pages.logs.user-logs');
})->name('logs.user');

Route::get('/honeypot-logs', function(){
    return view('pages.logs.honeypot-logs');
})->name('logs.honeypot');

// Route::get('/login', [AuthController::class, 'login']);
