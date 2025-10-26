<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::controller(AuthController::class)->group(function () {
    // Public routes
    Route::post('register', 'register');
    Route::post('login', 'login');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', 'me');
        Route::post('update', 'update');
        Route::post('logout', 'logout');
    });
});
