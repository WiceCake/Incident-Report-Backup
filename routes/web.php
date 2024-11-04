<?php

use App\Http\Controllers\ActionDocumentationController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompletedReportsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\PostIncidentController;
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
    Route::post('/incident-reports/draft', [IncidentReportController::class, 'draft'])->name('report.incident.draft');
    Route::post('/incident-reports/approve', [IncidentReportController::class, 'approve'])->name('report.incident.approve');

    // Incident Action and Documentation
    Route::get('/action-documentation', [ActionDocumentationController::class, 'index'])->name('report.action_documentation');
    Route::get('/action-documentation/{id}', [ActionDocumentationController::class, 'show'])->name('report.action_documentation.view');
    Route::post('/action-documentation/log-progress', [ActionDocumentationController::class, 'logProgress'])->name('report.action_documentation.logging');
    Route::post('/action-documentation/mitigate', [ActionDocumentationController::class, 'mitigateAttack'])->name('report.action_documentation.mitigate');
    Route::post('/action-documentation/post_assessment', [ActionDocumentationController::class, 'postAssessment'])->name('report.action_documentation.post_assessment');

    // Post Incident Report
    Route::get('/post-incident', [PostIncidentController::class, 'index'])->name('report.post_incident');
    Route::get('/post-incident/{id}', [PostIncidentController::class, 'show'])->name('report.post_incident.view');
    Route::post('/post-incident/close', [PostIncidentController::class, 'closeReport'])->name('report.post_incident.close');

    // Completed Reports
    Route::get('/completed-reports', [CompletedReportsController::class, 'index'])->name('report.completed_reports');
    Route::get('/completed-reports/{id}', [CompletedReportsController::class, 'show'])->name('report.post_incident.view');

    // Logs

    Route::get('/user-logs', function () {
        return view('pages.logs.user-logs');
    })->name('logs.user');

    Route::get('/honeypot-logs', function () {
        return view('pages.logs.honeypot-logs');
    })->name('logs.honeypot');

    Route::get('/php_info', function (){
        phpinfo();
    });
});



// Manage Reports



// Route::get('/incident-reports/{id}/edit', function(){
//     return view('pages.reports.incident-reports.edit-report');
// })->name('report.incident.edit');


// Route::get('/login', [AuthController::class, 'login']);
