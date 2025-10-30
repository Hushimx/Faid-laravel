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

    // Profile Routes
    Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit')->defaults('breadcrumbs', [
        ['name' => 'Edit Profile']
    ]);
    Route::patch('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::patch('profile/picture', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePicture'])->name('profile.picture.update');

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
