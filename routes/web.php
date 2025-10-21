<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;


// Login Routes
Route::prefix('login')->controller(LoginController::class)->group(function () {
    Route::get('/', 'index')->name('login');
    Route::post('/', 'login')->name('login');
});

// Dashboard Route
Route::get('', [DashboardController::class, 'index'])->name('dashboard')->defaults('breadcrumbs', [
    ['name' => 'Dashboard']
]);
