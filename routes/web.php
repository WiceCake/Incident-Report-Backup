<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SecurityEventsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Security Events

Route::get('/security-events', [SecurityEventsController::class, 'index'])->name('report.security');

Route::get('/incident-reports/create', function(){
    return view('pages.reports.incident-report.create-report');
})->name('report.security.create');

Route::get('/security-events/{id}', [SecurityEventsController::class, 'show'])->name('report.security.view');

// Manage Reports

Route::get('/manage-reports', function(){
    return view('pages.reports.manage-reports');
})->name('report.manage');

Route::get('/manage-reports/{id}/edit', function(){
    return view('pages.reports.manage-report.edit-report');
})->name('report.manage.edit');

Route::get('/manage-reports/{id}', function(){
    return view('pages.reports.manage-report.view-report');
})->name('report.manage.view');

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
