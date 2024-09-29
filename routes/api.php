<?php

use App\Http\Controllers\api\IncidentReportController;
use App\Http\Controllers\api\ThreatDetectionController;
use App\Http\Controllers\api\LogsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/v1')->group(function(){

    // Threats Api
    Route::get('/logged_in', [ThreatDetectionController::class, 'logged_in']);
    Route::get('/logged_in/attempt', [ThreatDetectionController::class, 'logged_in_attempt']);
    Route::get('/hp_events', [ThreatDetectionController::class, 'hp_events']);
    Route::get('/devices', [ThreatDetectionController::class, 'devices']);
    Route::get('/detection/weekly', [ThreatDetectionController::class, 'weeklyDetection']);
    Route::get('/cookies', [ThreatDetectionController::class, 'userCookies']);
    Route::get('/threats/all', [ThreatDetectionController::class, 'allThreats']);

    // Incident Report Api
    Route::get('/incident/reports', [IncidentReportController::class, 'lists']);

    // Edited by Eric
    Route::get('/logs/honeypot', [LogsController::class, 'honeypot_logs']);
});
