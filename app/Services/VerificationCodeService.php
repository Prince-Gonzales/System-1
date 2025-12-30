<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class VerificationCodeService
{
    public function generateCode(?int $length = null): string
    {
        $length = $length ?? (int) config('auth_custom.code_length', 6);

        return str_pad((string) random_int(0, 10 ** $length - 1), $length, '0', STR_PAD_LEFT);
    }

    public function ensureCanRequest(User $user): ?string
    {
        $maxAttempts = (int) config('auth_custom.verification.max_attempts', 5);

        if (($user->request_attempts ?? 0) >= $maxAttempts) {
            return 'Maximum attempts reached.';
        }

        $waitTimes = config('auth_custom.verification.wait_times', [1, 2, 3, 4, 5]);
        $attempts = $user->request_attempts ?? 0;
        $waitTimeMinutes = $waitTimes[$attempts] ?? end($waitTimes);

        if ($user->verification_requested_at) {
            $secondsSince = now()->diffInSeconds(Carbon::parse($user->verification_requested_at));
            if ($secondsSince < ($waitTimeMinutes * 60)) {
                return 'Please wait before requesting again.';
            }
        }

        return null;
    }

    public function send(User $user, string $subject = 'Your Verification Code'): string
    {
        $code = $this->generateCode();

        try {
            Mail::to($user->email)->send(new VerificationCodeMail($code, $subject));
        } catch (\Exception $e) {
            // If email fails, log it but still save the code so user can see it in logs for testing
            \Log::error('Email sending failed for user ' . $user->email . ': ' . $e->getMessage());
            \Log::info('Verification code for ' . $user->email . ': ' . $code);
            
            // Re-throw the exception so the controller can handle it
            throw $e;
        }

        $user->forceFill([
            'verification_code' => $code,
            'verification_requested_at' => Carbon::now(),
            'verification_status' => 'pending',
            'request_attempts' => ($user->request_attempts ?? 0) + 1,
        ])->save();

        return $code;
    }
}

