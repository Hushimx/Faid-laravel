<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Route;


// Login Routes
Route::prefix('login')->middleware('guest')->controller(LoginController::class)->group(function () {
    Route::get('/', 'index')->name('login');
    Route::post('/', 'login')->name('login');
});


Route::middleware(['auth'])->group(function () {

    // Logout Route
    Route::get('logout', [DashboardController::class, 'logout'])->name('logout');

    // Dashboard Route
    Route::get('', [DashboardController::class, 'index'])->name('dashboard')->defaults('breadcrumbs', [
        ['name' => 'Dashboard']
    ]);

});
