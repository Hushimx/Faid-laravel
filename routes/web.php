<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\TicketMessageController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\ChatReportController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


Route::view('/test', 'test');

// Public Routes

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

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


// Admin Panel Routes - All under /admin prefix
Route::prefix('admin')->group(function () {
    
    // Login Routes
    Route::middleware('guest')->controller(LoginController::class)->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login')->name('login');
    });

    // Authenticated Admin Routes
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
            ['name' => __('dashboard.dashboard')]
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

    // Banners Routes
    Route::prefix('banners')->name('banners.')->controller(BannerController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => 'Banners']
        ]);
        Route::post('/', 'store')->name('store');
        Route::post('/update-order', 'updateOrder')->name('update-order');
        Route::put('/{banner}', 'update')->name('update');
        Route::delete('/{banner}', 'destroy')->name('destroy');
    });

    // Services Routes
    Route::prefix('services')->name('services.')->controller(ServiceController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => 'Services']
        ]);
        Route::get('/{service}', 'show')->name('show')->defaults('breadcrumbs', [
            ['name' => 'Services', 'url' => 'services.index'],
            ['name' => 'View Service']
        ]);
        Route::get('/{service}/edit', 'edit')->name('edit')->defaults('breadcrumbs', [
            ['name' => 'Services', 'url' => 'services.index'],
            ['name' => 'Edit Service']
        ]);
        Route::put('/{service}', 'update')->name('update');
        Route::post('/{service}/status', 'updateStatus')->name('status.update');
        Route::delete('/{service}', 'destroy')->name('destroy');
        Route::delete('/{service}/reviews/{review}', 'destroyReview')->name('reviews.destroy');
    });

    // Products Routes
    // Route::prefix('products')->name('products.')->controller(ProductController::class)->group(function () {
    //     Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
    //         ['name' => 'Products']
    //     ]);
    //     Route::get('/{product}', 'show')->name('show')->defaults('breadcrumbs', [
    //         ['name' => 'Products', 'url' => 'products.index'],
    //         ['name' => 'View Product']
    //     ]);
    //     Route::get('/{product}/edit', 'edit')->name('edit')->defaults('breadcrumbs', [
    //         ['name' => 'Products', 'url' => 'products.index'],
    //         ['name' => 'Edit Product']
    //     ]);
    //     Route::put('/{product}', 'update')->name('update');
    //     Route::post('/{product}/status', 'updateStatus')->name('status.update');
    //     Route::delete('/{product}', 'destroy')->name('destroy');
    // });
    // Users Routes
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/all', 'index')->name('all')->defaults('breadcrumbs', [
            ['name' => __('dashboard.users')],
            ['name' => 'All Users']
        ]);
        Route::get('/admins', 'admins')->name('admins')->defaults('breadcrumbs', [
            ['name' => __('dashboard.users')],
            ['name' => 'Admins Management']
        ]);
        Route::get('/users', 'users')->name('users')->defaults('breadcrumbs', [
            ['name' => __('dashboard.users')],
            ['name' => 'Users Management']
        ]);
        Route::get('/vendors', 'vendors')->name('vendors')->defaults('breadcrumbs', [
            ['name' => __('dashboard.users')],
            ['name' => 'Vendors Management']
        ]);
        Route::get('/create', 'create')->name('create')->defaults('breadcrumbs', [
            ['name' => __('dashboard.users')],
            ['name' => 'Users Management', 'url' => 'users.all'],
            ['name' => 'Create User']
        ]);
        Route::post('/', 'store')->name('store');
        Route::get('/{user}/edit', 'edit')->name('edit')->defaults('breadcrumbs', [
            ['name' => 'Users Management', 'url' => 'users.all'],
            ['name' => 'Edit User']
        ]);
        Route::put('/{user}', 'update')->name('update');
        Route::delete('/{user}', 'destroy')->name('destroy');
        Route::post('/{user}/ban', 'ban')->name('ban');
        Route::post('/{user}/unban', 'unban')->name('unban');
    });

    // Roles Routes
    Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => __('dashboard.users')],
            ['name' => 'Roles Management']
        ]);
        Route::get('/create', 'create')->name('create')->defaults('breadcrumbs', [
            ['name' => __('dashboard.users')],
            ['name' => 'Roles Management', 'url' => 'roles.index'],
            ['name' => 'Create Role']
        ]);
        Route::post('/', 'store')->name('store');
        Route::get('/{role}/edit', 'edit')->name('edit')->defaults('breadcrumbs', [
            ['name' => __('dashboard.users')],
            ['name' => 'Roles Management', 'url' => 'roles.index'],
            ['name' => 'Edit Role']
        ]);
        Route::put('/{role}', 'update')->name('update');
        Route::delete('/{role}', 'destroy')->name('destroy');
    });

    // Tickets Routes (Admin only)
    Route::prefix('tickets')->name('tickets.')->controller(TicketController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => __('dashboard.Tickets')]
        ]);
        Route::get('/{ticket}', 'show')->name('show')->defaults('breadcrumbs', [
            ['name' => __('dashboard.Tickets'), 'url' => 'tickets.index'],
            ['name' => __('dashboard.Ticket')]
        ]);
        Route::put('/{ticket}', 'update')->name('update');
        Route::delete('/{ticket}', 'destroy')->name('destroy');
    });

    // Ticket Messages Routes
    Route::prefix('ticket-messages')->name('ticket-messages.')->controller(TicketMessageController::class)->group(function () {
        Route::get('/ticket/{ticket}', 'index')->name('index');
        Route::post('/ticket/{ticket}', 'store')->name('store');
        Route::post('/ticket/{ticket}/mark-read', 'markAsRead')->name('mark-read');
    });

    // Chats Routes (Admin only)
    Route::prefix('chats')->name('chats.')->controller(ChatController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => __('dashboard.Chats')]
        ]);
        Route::get('/{chat}', 'show')->name('show')->defaults('breadcrumbs', [
            ['name' => __('dashboard.Chats'), 'url' => 'chats.index'],
            ['name' => __('dashboard.Chat')]
        ]);
    });

    // Chat Reports Routes (Admin only)
    Route::prefix('chat-reports')->name('chat-reports.')->controller(ChatReportController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => __('dashboard.Chat Reports')]
        ]);
        Route::get('/{report}', 'show')->name('show')->defaults('breadcrumbs', [
            ['name' => __('dashboard.Chat Reports'), 'url' => 'chat-reports.index'],
            ['name' => __('dashboard.Chat Report')]
        ]);
        Route::post('/{report}/ban', 'banUser')->name('ban');
        Route::post('/{report}/dismiss', 'dismiss')->name('dismiss');
    });

    // Offers (Admin)
    Route::prefix('offers')->name('offers.')->controller(\App\Http\Controllers\Admin\OfferController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{offer}', 'update')->name('update');
        Route::delete('/{offer}', 'destroy')->name('destroy');
    });

    // Notifications (Admin)
    Route::prefix('notifications')->name('notifications.')->controller(\App\Http\Controllers\Admin\NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => __('dashboard.Notifications')]
        ]);
        Route::get('/create', 'create')->name('create')->defaults('breadcrumbs', [
            ['name' => __('dashboard.Notifications'), 'url' => 'notifications.index'],
            ['name' => __('dashboard.Send Notification')]
        ]);
        Route::post('/', 'store')->name('store');
        Route::get('/{notification}', 'show')->name('show')->defaults('breadcrumbs', [
            ['name' => __('dashboard.Notifications'), 'url' => 'notifications.index'],
            ['name' => __('dashboard.Notification Details')]
        ]);
    });

    // Vendor Applications (Admin)
    Route::prefix('vendor-applications')->name('vendor-applications.')->controller(\App\Http\Controllers\Admin\VendorApplicationController::class)->group(function () {
        Route::get('/', 'index')->name('index')->defaults('breadcrumbs', [
            ['name' => 'Vendor Applications']
        ]);
        Route::get('/{vendorApplication}', 'show')->name('show')->defaults('breadcrumbs', [
            ['name' => 'Vendor Applications', 'url' => 'vendor-applications.index'],
            ['name' => 'Application Details']
        ]);
        Route::post('/{vendorApplication}/approve', 'approve')->name('approve');
        Route::post('/{vendorApplication}/reject', 'reject')->name('reject');
    });
    });
});

// Public Routes (placed after authenticated routes to avoid conflicts)
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('wa', function () {

    $access_token = env('WHATSAPP_ACCESS_TOKEN');
    $create_instance_url = 'https://whatsapp.myjarak.com/api/create_instance?access_token=' . $access_token;
    // return $create_instance_url;
    // dd($create_instance_url);
    $Instance = Http::post($create_instance_url);
    dd($Instance->json());

    // return $Instance->body();
});


Broadcast::routes();
