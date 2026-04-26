<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required', 'string'],
        ]);

        $throttleKey = strtolower($request->input('email')) . '|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam $seconds detik.",
            ]);
        }

        $role = $credentials['role'];
        unset($credentials['role']);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $user = Auth::user();

            if ($user->role !== $role) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'role' => 'Role yang Anda pilih tidak sesuai dengan akun Anda.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey);
        $retriesLeft = \Illuminate\Support\Facades\RateLimiter::retriesLeft($throttleKey, 5);

        throw ValidationException::withMessages([
            'email' => "Password/email salah. Sisa kesempatan login: $retriesLeft kali lagi.",
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
