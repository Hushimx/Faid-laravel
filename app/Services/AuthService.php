<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class AuthService
{
    public function validateLogin(Request $request): array
    {
        return $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'regex:/^[a-zA-Z0-9]+$/'],
            'remember' => ['sometimes', 'boolean'],
        ]);
    }

    public function throttleKey(Request $request): string
    {
        return 'login:' . Str::lower($request->input('email')) . '|' . $request->ip();
    }

    public function tooManyAttempts(string $key, int $max = 5): bool
    {
        return RateLimiter::tooManyAttempts($key, $max);
    }

    public function hit(string $key, int $seconds = 60): void
    {
        RateLimiter::hit($key, $seconds);
    }

    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }

    public function attempt(array $credentials, bool $remember = false): bool
    {
        return Auth::attempt($credentials, $remember);
    }
}
