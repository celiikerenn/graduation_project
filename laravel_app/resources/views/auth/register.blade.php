@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')
<div class="auth-glass auth-card">
    <h2 class="auth-card__title">Create account</h2>
    <p class="auth-card__subtitle">Start managing your finances with FinTrack</p>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Jane Smith" required>
            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@email.com" required>
            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            @include('partials.password-input', [
                'id' => 'password',
                'name' => 'password',
                'placeholder' => 'At least 6 characters',
                'required' => true,
                'minlength' => 6,
            ])
            @error('password') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            @include('partials.password-input', [
                'id' => 'password_confirmation',
                'name' => 'password_confirmation',
                'placeholder' => '••••••••',
                'required' => true,
                'minlength' => 6,
            ])
        </div>
        <button type="submit" class="btn btn-primary">Sign Up</button>
    </form>
    <p class="auth-form-footer">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </p>
</div>
@endsection
