<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Support\EmailCipher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.signin');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors(['general' => 'Invalid email or password'])->withInput();
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['general' => 'Invalid email or password'])->withInput();
        }

        Auth::login($user, $remember);

        $request->session()->regenerate();

        $request->session()->put('email_encrypted', EmailCipher::encrypt($request->user()->email));
        $request->session()->put('last_activity', now()->timestamp);

        if ($remember) {
            $days = (int) config('auth_custom.remember_days', 30);
            Cookie::queue(
                Cookie::make(
                    'remember_me',
                    base64_encode($request->user()->id),
                    $days * 24 * 60,
                    '/',
                    null,
                    true,
                    true,
                    false,
                    'Lax'
                )
            );
        }

        return redirect()->intended(route('home'));
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        Cookie::queue(Cookie::forget('remember_me'));

        return redirect()->route('login');
    }
}

