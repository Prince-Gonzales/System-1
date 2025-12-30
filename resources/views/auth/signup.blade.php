@extends('layouts.base', ['title' => 'Sign Up', 'assets' => ['resources/css/auth_styles.css', 'resources/css/globals.css', 'resources/js/app.js']])

@section('content')
    <div class="container">
        <div class="left">
            <h2>Join Us!</h2>
            <p>Create an account to get started.</p>
        </div>
        <div class="right">
            <h2>Sign Up</h2>

            @if ($errors->any())
                <div class="error-messages">
                    @foreach ($errors->all() as $error)
                        <p class="error">{{ $error }}</p>
                    @endforeach
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

            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" name="full_name" value="{{ old('full_name') }}" placeholder="Full name" required>
                    @error('full_name')
                        <p class="error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="input-group">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
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
                <div class="input-group">
                    <input type="password" name="password_confirmation" placeholder="Confirm password" required>
                    <i class="fa fa-eye"></i>
                </div>
                <button type="submit" class="btn">Sign Up</button>
            </form>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <a href="{{ route('google.redirect') }}" class="btn-google">
                <img src="{{ asset('assets/images/google-logo.svg') }}" alt="Google" width="20" height="20">
                Continue with Google
            </a>
            
            <div class="register">
                Already have an account? <a href="{{ route('login') }}">Sign In</a>
            </div>
        </div>
    </div>
@endsection

