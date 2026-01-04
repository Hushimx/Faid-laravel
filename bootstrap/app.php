<?php

use App\Support\ApiResponse;
use App\Http\Middleware\ForceJson;
use App\Http\Middleware\SetApiLocale;
use Illuminate\Foundation\Application;
use App\Http\Middleware\EnsureVerifiedUser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            ForceJson::class,
            SetApiLocale::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLang::class,
        ]);
        $middleware->alias([
            'ensure-verified-user' => EnsureVerifiedUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $apiHandler = new \App\Exceptions\ApiExceptionHandler();

        $exceptions->renderable(function (\Throwable $e) use ($apiHandler) {
            $request = request();
            if ($apiHandler->shouldHandle($request)) {
                return $apiHandler->handle($e, $request);
            }
        });

        // Handle authentication exceptions - redirect to admin login
        $exceptions->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            // Redirect to admin login for admin routes
            if ($request->is('admin*')) {
                return redirect()->route('login');
            }
            
            // Default redirect for other routes
            return redirect()->route('login');
        });

        // Handle authorization exceptions for web routes
        $exceptions->renderable(function (AuthorizationException $e, $request) {
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', __('dashboard.You do not have permission to access this resource'));
            }
        });
    })->create();
