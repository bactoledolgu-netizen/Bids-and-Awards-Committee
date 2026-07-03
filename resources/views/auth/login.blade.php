@extends('layouts.guest')

@section('content')
<div class="auth-card">
    <div class="auth-panel">
        <div class="auth-header">
            <div class="auth-logo"></div>
            <h1 class="auth-title">BAC Minutes and Attendance Management System</h1>
            <p class="auth-subtitle">Bids and Awards Committee — Toledo City Hall</p>
        </div>

        @if ($errors->any())
            <div class="error-banner" role="alert">
                <span class="error-banner__icon">⚠</span>
                <div class="error-banner__text">{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate x-data="{ showPassword: false }">
            @csrf

            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" required autofocus autocomplete="username" value="{{ old('username') }}" />
            </div>

            <div class="form-group password-field">
                <label for="password">Password</label>
                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password" />
                <button type="button" class="password-toggle" @click="showPassword = !showPassword" x-text="showPassword ? 'Hide' : 'Show'"></button>
            </div>

            <!-- <div class="action-row">
                <label class="checkbox-field">
                    <input type="checkbox" name="remember" />
                    Remember me
                </label>
                @if (Route::has('password.request'))
                    <a class="forget-link" href="{{ route('password.request') }}">Forgot your password?</a>
                @endif
            </div> -->

            <button type="submit" class="submit-button">Login</button>
        </form>
    </div>
</div>
@endsection
