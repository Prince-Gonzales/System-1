@extends('layouts.base', ['title' => 'Recover Password', 'assets' => ['resources/css/auth_styles.css', 'resources/css/globals.css', 'resources/js/app.js']])

@section('content')
    <div class="container">
        <div class="left">
            <h2>Forgot Your Password?</h2>
            <p>Enter your new password</p>
        </div>
        <div class="right">
            <h2>Recover Password</h2>

            @if ($errors->has('general'))
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

            @if ($verified ?? false)
                <form action="{{ route('password.handle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="identifier" value="{{ $identifier }}">
                    <div class="input-group">
                        <input type="password" name="password" placeholder="New password" required>
                        <i class="fa fa-eye"></i>
                    </div>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" placeholder="Confirm password" required>
                        <i class="fa fa-eye"></i>
                    </div>

                    <button type="submit" class="btn">Reset Password</button>
                </form>
            @else
                <form action="{{ route('password.handle') }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="btn">Reset Password</button>
                </form>
            @endif

            <div class="register">
                Remembered your password? <a href="{{ route('login') }}">Sign In</a>
            </div>
        </div>
    </div>
@endsection

