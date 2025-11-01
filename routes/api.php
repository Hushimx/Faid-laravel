<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ProductController;
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

// Categories Routes (Public - Read-only, Active categories only)
Route::controller(CategoryController::class)->group(function () {
    Route::get('categories', 'index');
    Route::get('categories/tree', 'tree');
    Route::get('categories/parents', 'parents');
    Route::get('categories/{category}', 'show');
});

// Services Routes
Route::controller(ServiceController::class)->group(function () {
    // Public routes (visible services only)
    Route::get('services', 'index');
    Route::get('services/{service}', 'show');

    // Vendor routes only (authenticated vendors)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('services', 'store');
        Route::put('services/{service}', 'update');
        Route::delete('services/{service}', 'destroy');
    });
});

// Products Routes
Route::controller(ProductController::class)->group(function () {
    // Public routes (visible products only)
    Route::get('products', 'index');
    Route::get('products/{product}', 'show');

    // Vendor routes only (authenticated vendors)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('products', 'store');
        Route::put('products/{product}', 'update');
        Route::delete('products/{product}', 'destroy');
    });
});
