<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Throwable;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.signup');
    }

    public function store(RegisterRequest $request, VerificationCodeService $codeService): RedirectResponse
    {
        $data = $request->validated();
        $requireVerification = (bool) config('auth_custom.require_verification', false);

        $user = User::create([
            'name' => $data['full_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'verification_status' => $requireVerification ? 'pending' : 'verified',
            'request_attempts' => 0,
        ]);

        if ($requireVerification) {
            try {
                $codeService->send($user, 'Your Verification Code');
            } catch (Throwable $e) {
                return back()->withErrors(['general' => 'Failed to send verification email. Please try again.']);
            }
            session(['verification_email' => $user->email]);

            return redirect()
                ->route('verify.email')
                ->with('success', 'Account created. A verification code was sent to your email.');
        }

        return redirect()
            ->route('login')
            ->with('success', 'Account created successfully! Please sign in.');
    }
}

