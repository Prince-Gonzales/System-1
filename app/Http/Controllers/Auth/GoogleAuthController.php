<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists with this Google ID
            $user = User::where('google_id', $googleUser->getId())->first();

            if ($user) {
                // User exists, update their profile picture and log them in
                if ($googleUser->getAvatar()) {
                    $user->profile_picture = $googleUser->getAvatar();
                    $user->save();
                }
                Auth::login($user, true);
                return redirect()->route('home')->with('success', 'Successfully signed in with Google!');
            }

            // Check if user exists with this email
            $existingUser = User::where('email', $googleUser->getEmail())->first();

            if ($existingUser) {
                // Link Google account to existing user
                $existingUser->google_id = $googleUser->getId();
                $existingUser->email_verified_at = now();
                $existingUser->verification_status = 'verified';
                if ($googleUser->getAvatar()) {
                    $existingUser->profile_picture = $googleUser->getAvatar();
                }
                $existingUser->save();
                Auth::login($existingUser, true);
                return redirect()->route('home')->with('success', 'Successfully linked Google account!');
            }

            // Create new user
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
                'verification_status' => 'verified',
                'profile_picture' => $googleUser->getAvatar(),
                'password' => null, // Google users don't need a password
            ]);

            Auth::login($user, true);
            return redirect()->route('home')->with('success', 'Successfully signed up with Google!');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Unable to authenticate with Google. Please try again.');
        }
    }
}
