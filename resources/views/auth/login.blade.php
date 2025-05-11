@extends('layouts.guest')

@section('title', 'Login')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
    <div class="text-center mb-6">
            <img src="{{ asset('images/Logo_The_Daily_Wash-removebg-preview.png') }}" alt="Logo" class="mx-auto h-55 w-auto" />
    </div>
        <div class="container">
        <h2>Login</h2>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <!-- Password -->
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <!-- Remember Me -->
            <div class="form-check">
                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                <label class="form-check-label" for="remember_me">
                    {{ __('Remember me') }}
                </label>
            </div>

            <div class="actions">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                @endif
                <button type="submit">{{ __('Log in') }}</button>
            </div>
        </form>
    </div>
@endsection
