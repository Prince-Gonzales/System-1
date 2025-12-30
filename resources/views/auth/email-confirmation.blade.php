@extends('layouts.base', ['title' => 'Email Confirmation', 'assets' => ['resources/css/auth_styles.css', 'resources/css/globals.css', 'resources/js/app.js']])

@section('content')
    <div class="container">
        <div class="left">
            <h2>Email Verification</h2>
            <p>Enter the verification code sent to your email</p>
            <p class="user-email">{{ $email }}</p>
        </div>
        <div class="right">
            <h2>Verify Your Email</h2>

            @if (session('error'))
                <div class="error-messages">
                    <p class="error">{{ session('error') }}</p>
                    @if (session('debug_code'))
                        <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">
                            <p style="margin: 0; color: #856404; font-weight: bold;">⚠ Development Mode - Email Failed</p>
                            <p style="margin: 5px 0 0 0; color: #856404;">Your verification code is: <strong style="font-size: 20px;">{{ session('debug_code') }}</strong></p>
                            <p style="margin: 5px 0 0 0; color: #856404; font-size: 12px;">Use this code to continue testing. Fix Gmail configuration to receive emails.</p>
                        </div>
                    @endif
                </div>
            @elseif ($errors->has('general'))
                <div class="error-messages">
                    <p class="error">{{ $errors->first('general') }}</p>
                </div>
            @elseif ($errors->any())
                <div class="error-messages">
                    <p class="error">{{ $errors->first() }}</p>
                </div>
            @elseif (session('success'))
                <div class="success-messages">
                    <p class="success">{{ session('success') }}</p>
                </div>
            @endif

            <form action="{{ route('password.handle') }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Enter verification code" required>
                </div>
                <button type="submit" class="btn">Verify</button>
            </form>
            <div class="register">
                Didn't receive the code?
                <form action="{{ route('password.handle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" class="btn-link">Request a new one</button>
                </form>
            </div>
        </div>
    </div>
@endsection

