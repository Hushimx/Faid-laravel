<?php

use App\Http\Middleware\ForceJson;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            ForceJson::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SetLang::class,
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
    })->create();
