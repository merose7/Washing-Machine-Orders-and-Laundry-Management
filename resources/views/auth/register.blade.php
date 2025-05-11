@extends('layouts.guest')

@section('title', 'Register')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endpush

@section('content')
    <div class="logo-container text-center mb-6">
        <img src="{{ asset('images/Logo_The_Daily_Wash-removebg-preview.png') }}" alt="Logo" class="mx-auto h-20 w-auto" />
    </div>
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <label for="name">{{ __('Name') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <!-- Email Address -->
            <label for="email">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <!-- Password -->
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <!-- Confirm Password -->
            <label for="password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            @error('password_confirmation')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <div class="actions">
                <a href="{{ route('login') }}">{{ __('Already registered?') }}</a>
                <button type="submit">{{ __('Register') }}</button>
            </div>
        </form>
    </div>
@endsection
