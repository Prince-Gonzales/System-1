<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UploadProfilePictureRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->fresh();

        return view('user.profile', ['user' => $user]);
    }

    public function update(
        Request $request
    ): RedirectResponse {
        $user = $request->user()->fresh();

        if ($request->has('update-profile-picture')) {
            return $this->updateProfilePicture($request, $user);
        }

        if ($request->has('update-profile')) {
            $data = $request->validate((new UpdateProfileRequest())->rules());
            $user->update(['name' => $data['name']]);

            return back()->with('success', ['update-profile' => 'Profile name updated']);
        }

        if ($request->has('update-password')) {
            $data = $request->validate((new UpdatePasswordRequest())->rules());

            if (!Hash::check($data['current-password'], $user->password)) {
                return back()->withErrors(['update-password' => 'You supplied wrong current password'])->withInput();
            }

            $user->update(['password' => Hash::make($data['password'])]);

            return back()->with('success', ['update-password' => 'Password updated']);
        }

        if ($request->has('delete')) {
            Auth::logout();
            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return back();
    }

    private function updateProfilePicture(Request $request, $user): RedirectResponse
    {
        try {
            $validated = $request->validate((new UploadProfilePictureRequest())->rules());

            $file = $request->file('profile_picture');
            
            // Debug: Log file information
            Log::info('Profile picture upload attempt', [
                'user_id' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            $folder = 'uploads/' . Str::slug($user->name ?: 'user');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // Delete old profile picture if it exists and is not an external URL
            if ($user->profile_picture && !str_starts_with($user->profile_picture, 'http')) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $file->storeAs($folder, $filename, 'public');

            if (!$path) {
                throw new \Exception('Failed to store file');
            }

            // Debug: Log storage path
            Log::info('Profile picture stored', [
                'path' => $path,
                'storage_path' => storage_path('app/public/' . $path),
            ]);

            $user->update(['profile_picture' => $path]);

            return back()->with('success', ['update-profile' => 'Profile picture updated successfully!']);
        } catch (\Exception $e) {
            Log::error('Profile picture upload failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['profile_picture' => 'Failed to upload profile picture: ' . $e->getMessage()])->withInput();
        }
    }
}

