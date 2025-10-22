<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function __construct(private AuthService $auth)
    {
    }

    public function index()
    {
        return view('login');
    }

    public function login(Request $request)
    {

        $this->auth->validateLogin($request);

        $key = $this->auth->throttleKey($request);
        if ($this->auth->tooManyAttempts($key)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withInput($request->only('email', 'remember'))
                ->with('error', "Too many attempts. Please try again in {$seconds} seconds.");
        }

        $credentials = $request->only('email', 'password');
        $credentials['type'] = 'admin';

        $ok = $this->auth->attempt($credentials, $request->boolean('remember'));

        if ($ok) {

            $this->auth->clear($key);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Logged in successfully 👋');

        }

        $this->auth->hit($key);
        return back()
            ->withInput($request->only('email', 'remember'))
            ->with('error', 'Invalid credentials. Please try again.');
    }
}
