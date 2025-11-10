<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedUser
{
    /**
     * Handle an incoming request.
     *
     * Ensures that any authenticated user of type "user" or "vendor"
     * has verified their email before accessing protected routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && in_array($user->type, ['user', 'vendor'], true)) {
            if (is_null($user->email_verified_at)) {
                return ApiResponse::error(
                    'Email verification required',
                    ['verification' => ['Please verify your email to continue.']],
                    403
                );
            }
        }

        return $next($request);
    }
}

