<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Route::get('/health-check', function () {
    $config = [
        'db_default' => config('database.default'),
        'env_db_connection' => env('DB_CONNECTION'),
        'session_connection' => config('session.connection'),
        'pgsql_host' => config('database.connections.pgsql.host'),
    ];
    try {
        $pdo = DB::connection()->getPdo();
        Log::info('Database connection successful', $config);
        return response()->json([
            'status' => 'ok',
            'message' => 'Database connection successful',
            'context' => $config,
        ]);
    } catch (\Exception $e) {
        Log::error('Database connection failed', ['error' => $e->getMessage()] + $config);
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed: ' . $e->getMessage(),
            'context' => $config,
        ], 500);
    }
});

Route::middleware(['auth', 'idle'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home', [HomeController::class, 'index']);

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::post('/update-profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/sign-in', [LoginController::class, 'show'])->name('login');
    Route::post('/sign-in', [LoginController::class, 'store']);

    Route::get('/sign-up', [RegisterController::class, 'show'])->name('register');
    Route::post('/sign-up', [RegisterController::class, 'store']);

    Route::get('/verify-email', [VerificationController::class, 'show'])->name('verify.email');
    Route::post('/verify-email', [VerificationController::class, 'handle']);

    Route::get('/reset-password', [PasswordResetController::class, 'showRecover'])->name('password.request');
    Route::post('/reset-password', [PasswordResetController::class, 'handle'])->name('password.handle');

    Route::get('/email-confirmation', [PasswordResetController::class, 'showEmailConfirmation'])
        ->name('password.confirmation');

    // Google OAuth routes
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');
});

Route::fallback(fn () => abort(404));
