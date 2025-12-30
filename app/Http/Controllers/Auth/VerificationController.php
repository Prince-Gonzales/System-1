<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\VerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class VerificationController extends Controller
{
    public function show(Request $request): RedirectResponse|View
    {
        $email = $request->session()->get('verification_email');

        if (!$email) {
            return redirect()->route('register');
        }

        return view('auth.verify', ['email' => $email]);
    }

    public function handle(Request $request, VerificationCodeService $codeService): RedirectResponse
    {
        return $request->filled('code')
            ? $this->verifyCode($request, $codeService)
            : $this->requestNewCode($request, $codeService);
    }

    private function verifyCode(Request $request, VerificationCodeService $codeService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:' . (int) config('auth_custom.code_length', 6)],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['general' => 'User not found.'])->withInput();
        }

        if ($user->verification_code !== $validated['code']) {
            return back()->withErrors(['general' => 'Invalid verification code.'])->withInput();
        }

        $user->forceFill([
            'verification_status' => 'verified',
            'verification_code' => null,
            'verification_requested_at' => null,
            'request_attempts' => 0,
            'email_verified_at' => now(),
        ])->save();

        $request->session()->forget('verification_email');

        return redirect()->route('login')->with('success', 'Email verified successfully.');
    }

    private function requestNewCode(Request $request, VerificationCodeService $codeService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['general' => 'User not found.']);
        }

        if ($user->is_verified) {
            return back()->withErrors(['general' => 'Email is already verified.']);
        }

        $throttleMessage = $codeService->ensureCanRequest($user);
        if ($throttleMessage) {
            return back()->withErrors(['general' => $throttleMessage]);
        }

        try {
            $codeService->send($user, 'Your Verification Code');
        } catch (Throwable $e) {
            return back()->withErrors(['general' => 'Failed to send email. Please try again.']);
        }
        $request->session()->put('verification_email', $user->email);

        return back()->with('success', 'Verification code has been sent.');
    }
}

