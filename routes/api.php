<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OfferController;



Route::controller(AuthController::class)->group(function () {
    // Public routes
    Route::post('register', 'register');
    Route::post('login', 'login');

    // OTP routes
    Route::post('send-otp', 'sendOtp')->middleware('throttle:1,1');
    Route::post('verify-otp', 'verifyOtp');
    Route::post('reset-password', 'resetPassword');

    // Protected routes
    Route::middleware(['auth:sanctum', 'ensure-verified-user'])->group(function () {
        Route::get('me', 'me');
        Route::post('update', 'update');
        Route::post('logout', 'logout');
    });
});

// Public offers
Route::get('/offers', [OfferController::class, 'index']);

// Categories Routes (Public - Read-only, Active categories only)
Route::controller(CategoryController::class)->group(function () {
    Route::get('categories', 'index');
    Route::get('categories/{category}', 'show');
});

// Services Routes
Route::controller(ServiceController::class)->group(function () {
    // Public routes (visible services only)
    Route::get('services', 'index');
    Route::get('services/{service}', 'show');

    // Reviews for services (public list, auth to post)
    Route::get('services/{service}/reviews', [\App\Http\Controllers\Api\ServiceReviewController::class, 'index']);
    Route::post('services/{service}/reviews', [\App\Http\Controllers\Api\ServiceReviewController::class, 'store'])->middleware(['auth:sanctum', 'ensure-verified-user']);

    // Vendor routes only (authenticated vendors)
    Route::middleware(['auth:sanctum', 'ensure-verified-user'])->group(function () {
        Route::post('services', 'store');
        Route::put('services/{service}', 'update');
        Route::delete('services/{service}', 'destroy');
    });
});

// Products Routes
// Route::controller(ProductController::class)->group(function () {
//     // Public routes (visible products only)
//     Route::get('products', 'index');
//     Route::get('products/{product}', 'show');

//     // Vendor routes only (authenticated vendors)
//     Route::middleware(['auth:sanctum', 'ensure-verified-user'])->group(function () {
//         Route::post('products', 'store');
//         Route::put('products/{product}', 'update');
//         Route::delete('products/{product}', 'destroy');
//     });
// });

// Tickets Routes (for users and vendors)
Route::middleware(['auth:sanctum', 'ensure-verified-user'])->controller(TicketController::class)->group(function () {
    Route::get('tickets', 'index');
    Route::post('tickets', 'store');
    Route::get('tickets/{ticket}', 'show');
    Route::put('tickets/{ticket}', 'update');
    Route::delete('tickets/{ticket}', 'destroy');
});

// Ticket Messages Routes
Route::middleware(['auth:sanctum', 'ensure-verified-user'])->controller(TicketMessageController::class)->group(function () {
    Route::get('tickets/{ticket}/messages', 'index');
    Route::post('tickets/{ticket}/messages', 'store');
    Route::post('tickets/{ticket}/messages/mark-read', 'markAsRead');
});

// Chat Routes
Route::middleware(['auth:sanctum', 'ensure-verified-user'])->controller(\App\Http\Controllers\Api\ChatController::class)->group(function () {
    Route::get('chats', 'chats');
    Route::post('chats/start', 'startChat');
    Route::get('chats/{chat}/messages', 'messages');
    Route::post('chats/{chat}/messages', 'send');
});
