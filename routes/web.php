<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\UserController;
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

    // Users Routes
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => 'Users Management']
        ]);
        Route::get('/create', 'create')->name('create')->defaults('breadcrumbs', [
            ['name' => 'Users Management', 'url' => 'users.index'],
            ['name' => 'Create User']
        ]);
        Route::post('/', 'store')->name('store');
        Route::get('/{user}/edit', 'edit')->name('edit')->defaults('breadcrumbs', [
            ['name' => 'Users Management', 'url' => 'users.index'],
            ['name' => 'Edit User']
        ]);
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
    });
});
