@extends('layouts.base', ['title' => 'Sign In', 'assets' => ['resources/css/auth_styles.css', 'resources/css/globals.css', 'resources/js/app.js']])

@section('content')
    <div class="container">
        <div class="left">
            <h2>Welcome back!</h2>
            <p>You can sign in to access your existing account.</p>
        </div>
        <div class="right">
            <h2>Sign In</h2>

            @if ($errors->has('general'))
                <div class="error-messages">
                    <p class="error">{{ $errors->first('general') }}</p>
                </div>
            @elseif ($errors->any())
                <div class="error-messages">
                    <p class="error">{{ $errors->first() }}</p>
                </div>
            @endif

            @if (session('success'))
                <div class="success-messages">
                    <p class="success">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="error-messages">
                    <p class="error">{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
                    @error('email')
                        <p class="error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="fa fa-eye"></i>
                    @error('password')
                        <p class="error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="options">
                    <label>
                        <input name="remember" type="checkbox" value="1"> Remember me</label>
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                </div>
                <button type="submit" class="btn">Sign In</button>
            </form>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <a href="{{ route('google.redirect') }}" class="btn-google">
                <img src="{{ asset('assets/images/google-logo.svg') }}" alt="Google" width="20" height="20">
                Continue with Google
            </a>
            
            <div class="register">
                New here? <a href="{{ route('register') }}">Create an Account</a>
            </div>
        </div>
    </div>
@endsection

