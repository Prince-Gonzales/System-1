@extends('layouts.base', ['title' => 'Verify Email', 'assets' => ['resources/css/auth_styles.css', 'resources/css/globals.css', 'resources/js/app.js']])

@section('content')
    <div class="container">
        <div class="left">
            <h2>Email Verification</h2>
            <p>Enter the verification code sent to your email</p>
            <p class="user-email">{{ $email }}</p>
        </div>
        <div class="right">
            <h2>Verify Your Email</h2>

            @if ($errors->any())
                <div class="error-messages">
                    @foreach ($errors->all() as $error)
                        <p class="error">{{ $error }}</p>
                    @endforeach
                </div>
            @elseif (session('success'))
                <div class="success-messages">
                    <p class="success">{{ session('success') }}</p>
                </div>
            @endif

            <form action="{{ route('verify.email') }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Enter verification code" required>
                </div>
                <button type="submit" class="btn">Verify</button>
            </form>
            <div class="register">
                Didn't receive the code?
                <form action="{{ route('verify.email') }}" method="POST">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <button type="submit" class="btn-link">Request a new one</button>
                </form>
            </div>
        </div>
    </div>
@endsection

