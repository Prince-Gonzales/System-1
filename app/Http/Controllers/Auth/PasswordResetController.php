<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\VerificationCodeService;
use App\Support\EmailCipher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showRecover(Request $request): RedirectResponse|View
    {
        return view('auth.recover', [
            'verified' => (bool) $request->session()->get('email_verified'),
            'identifier' => $request->session()->get('email_identifier'),
        ]);
    }

    public function showEmailConfirmation(Request $request): RedirectResponse|View
    {
        $email = $request->session()->get('verification_email');

        if (!$email) {
            return redirect()->route('password.request');
        }

        return view('auth.email-confirmation', ['email' => $email]);
    }

    public function handle(Request $request, VerificationCodeService $codeService): RedirectResponse
    {

        if ($request->filled('password')) {
            return $this->resetPassword($request);
        }

        if ($request->filled('code')) {
            return $this->verifyResetCode($request);
        }

        return $this->requestResetCode($request, $codeService);
    }

    private function requestResetCode(Request $request, VerificationCodeService $codeService): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['general' => 'Email address not found'])->withInput();
        }

        $throttleMessage = $codeService->ensureCanRequest($user);
        if ($throttleMessage) {
            return back()->withErrors(['general' => $throttleMessage])->withInput();
        }

        try {
            $code = $codeService->send($user, 'Password Reset Verification Code');
            
            $request->session()->put('verification_email', $user->email);
            $request->session()->forget('email_verified');
            $request->session()->forget('email_identifier');
            
            return redirect()
                ->route('password.confirmation')
                ->with('success', 'Verification code sent to your email.');
        } catch (\Throwable $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
            
            // Generate code manually and save it for development/testing
            $code = $codeService->generateCode();
            $user->forceFill([
                'verification_code' => $code,
                'verification_requested_at' => \Carbon\Carbon::now(),
                'verification_status' => 'pending',
                'request_attempts' => ($user->request_attempts ?? 0) + 1,
            ])->save();
            
            \Log::info('Password reset code generated (email failed) for ' . $user->email . ': ' . $code);
            
            $errorMessage = 'Email service is currently unavailable. ';
            $errorMessage .= 'Please contact support or try again later.';
            
            // For development: show code on screen if email fails
            if (config('app.debug')) {
                $request->session()->put('verification_email', $user->email);
                $request->session()->forget('email_verified');
                $request->session()->forget('email_identifier');
                
                return redirect()
                    ->route('password.confirmation')
                    ->with('error', $errorMessage)
                    ->with('debug_code', $code)
                    ->with('debug_email', $user->email);
            }
            
            return back()->withErrors(['general' => $errorMessage])->withInput();
        }
    }

    private function verifyResetCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:' . (int) config('auth_custom.code_length', 6)],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['general' => 'Email address not found'])->withInput();
        }

        if ($user->verification_code !== $validated['code']) {
            return back()->withErrors(['general' => 'Invalid verification code'])->withInput();
        }

        $user->forceFill([
            'verification_status' => 'verified',
            'verification_code' => null,
            'verification_requested_at' => null,
            'request_attempts' => 0,
        ])->save();

        $request->session()->put('email_verified', true);
        $request->session()->put('email_identifier', EmailCipher::encrypt($user->email));

        return redirect()
            ->route('password.request')
            ->with('success', 'Email verified. You can now reset your password.');
    }

    private function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'identifier' => ['required', 'string'],
        ]);

        $email = EmailCipher::decrypt($validated['identifier']);

        if (!$email) {
            return redirect()->route('password.request')->withErrors(['general' => 'Invalid reset link. Please try again.']);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.request')->withErrors(['general' => 'Email address not found']);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'verification_code' => null,
            'verification_status' => 'verified',
            'request_attempts' => 0,
            'verification_requested_at' => null,
            'email_verified_at' => now(),
        ])->save();

        $request->session()->forget(['email_verified', 'email_identifier', 'verification_email']);

        return redirect()->route('login')->with('success', 'Password updated successfully.');
    }
}

