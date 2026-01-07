<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TicketMessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\CityController;



Route::controller(AuthController::class)->group(function () {
    // Public routes
    Route::post('register', 'register');
    Route::post('login', 'login');

    // OTP routes - limit to 1 request per minute
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

// Banners Routes (Public - Read-only, Active banners only)
Route::controller(BannerController::class)->group(function () {
    Route::get('banners', 'index');
    Route::get('banners/{banner}', 'show');
});

// Categories Routes (Public - Read-only, Active categories only)
Route::controller(CategoryController::class)->group(function () {
    Route::get('categories', 'index');
    Route::get('categories/{category}', 'show');
});

// Cities Routes (Public - Read-only)
Route::controller(CityController::class)->group(function () {
    Route::get('cities', 'index');
});

// Services Routes
Route::controller(ServiceController::class)->group(function () {
    // Public routes (visible services only)
    Route::get('services', 'index');
    Route::get('services/{service}', 'show');
    Route::get('services/{service}/related', 'related');

    // Reviews for services (public list, auth to post)
    Route::get('services/{service}/reviews', [\App\Http\Controllers\Api\ServiceReviewController::class, 'index']);
    Route::post('services/{service}/reviews', [\App\Http\Controllers\Api\ServiceReviewController::class, 'store'])->middleware(['auth:sanctum', 'ensure-verified-user']);

    // Vendor routes only (authenticated vendors)
    Route::middleware(['auth:sanctum', 'ensure-verified-user'])->group(function () {
        Route::post('services', 'store');
        Route::post('services/{service}/update', 'update'); // Use POST for updates to handle form-data
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
// Rate limiting: 10 ticket creations per hour, 60 requests per minute for other operations
Route::middleware(['auth:sanctum', 'ensure-verified-user'])->controller(TicketController::class)->group(function () {
    Route::get('tickets', 'index')->middleware('throttle:60,1');
    Route::post('tickets', 'store')->middleware('throttle:10,60'); // 10 per hour
    Route::get('tickets/{ticket}', 'show')->middleware('throttle:60,1');
    Route::put('tickets/{ticket}', 'update')->middleware('throttle:30,1'); // 30 updates per minute
    Route::delete('tickets/{ticket}', 'destroy')->middleware('throttle:10,1'); // 10 deletions per minute
});

// Ticket Messages Routes
// Rate limiting: Removed throttle for sending messages to allow normal conversation flow
// Increased throttle for reading to allow frequent polling (300 per minute = 5 per second)
Route::middleware(['auth:sanctum', 'ensure-verified-user'])->controller(TicketMessageController::class)->group(function () {
    Route::get('tickets/{ticket}/messages', 'index')->middleware('throttle:300,1'); // 300 per minute for frequent polling
    Route::post('tickets/{ticket}/messages', 'store'); // No throttle - allow normal messaging
    Route::post('tickets/{ticket}/messages/mark-read', 'markAsRead')->middleware('throttle:60,1');
});

// Chat Routes
Route::middleware(['auth:sanctum', 'ensure-verified-user'])->controller(\App\Http\Controllers\Api\ChatController::class)->group(function () {
    Route::get('chats', 'chats');
    Route::post('chats/start', 'startChat');
    Route::get('chats/{chat}/messages', 'messages');
    Route::post('chats/{chat}/messages', 'send');
});

// FCM Token Routes (Deprecated - Handled in Auth)
// Route::middleware(['auth:sanctum', 'ensure-verified-user'])->controller(\App\Http\Controllers\Api\FcmTokenController::class)->group(function () {
//     Route::get('fcm-tokens', 'index');
//     Route::post('fcm-tokens/register', 'register');
//     Route::delete('fcm-tokens', 'delete');
// });

// User Notifications
Route::middleware(['auth:sanctum', 'ensure-verified-user'])->controller(\App\Http\Controllers\Api\NotificationController::class)->group(function () {
    Route::get('notifications', 'index');
    Route::post('notifications/{id}/read', 'markAsRead');
    Route::post('notifications/read-all', 'markAllAsRead');
});

// Vendor Applications Routes
Route::middleware(['auth:sanctum', 'ensure-verified-user'])->controller(\App\Http\Controllers\Api\VendorApplicationController::class)->group(function () {
    Route::post('vendor-applications', 'store');
    Route::get('vendor-applications/my-application', 'show');
});
