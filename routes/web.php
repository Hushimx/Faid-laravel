<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;


// Language Route
Route::get('change-locale/{locale}', function ($locale) {
    // verify locale exists in supported languages
    try {
        $allowed = collect(locales())->pluck('code')->toArray();
    } catch (\Throwable $e) {
        $allowed = ['en', 'ar'];
    }
    if (!in_array($locale, $allowed, true)) {
        return redirect()->back();
    }

    // store in session for persistence
    session()->put('locale', $locale);
    session()->put('lang', $locale);

    // set for current request
    App::setLocale($locale);

    return redirect()->back();
})->name('change.locale');


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

    // Countries Routes
    Route::prefix('countries')->name('countries.')->controller(CountryController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => 'Countries']
        ]);
        Route::post('/', 'store')->name('store');
        Route::put('/{country}', 'update')->name('update');
        Route::delete('/{country}', 'destroy')->name('destroy');
    });

    // Cities Routes
    Route::prefix('cities')->name('cities.')->controller(CityController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => 'Cities']
        ]);
        Route::post('/', 'store')->name('store');
        Route::put('/{city}', 'update')->name('update');
        Route::delete('/{city}', 'destroy')->name('destroy');
    });

    // Categories Routes
    Route::prefix('categories')->name('categories.')->controller(CategoryController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => 'Categories']
        ]);
        Route::post('/', 'store')->name('store');
        Route::put('/{category}', 'update')->name('update');
        Route::delete('/{category}', 'destroy')->name('destroy');
    });
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
