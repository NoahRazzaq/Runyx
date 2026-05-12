<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StravaAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/auth/strava', [StravaAuthController::class, 'redirect'])->name('strava.redirect');
Route::get('/auth/strava/callback', [StravaAuthController::class, 'callback'])->name('strava.callback');
Route::post('/auth/disconnect', [StravaAuthController::class, 'disconnect'])->name('strava.disconnect');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
