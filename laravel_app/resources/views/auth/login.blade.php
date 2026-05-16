@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
<div class="auth-glass auth-card">
    <h2 class="auth-card__title">Welcome back</h2>
    <p class="auth-card__subtitle">Sign in to your account</p>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@email.com" required autofocus>
            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            @include('partials.password-input', [
                'id' => 'password',
                'name' => 'password',
                'placeholder' => '••••••••',
                'required' => true,
            ])
            @error('password') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Sign In</button>
    </form>
    <p class="auth-form-footer">
        Don&rsquo;t have an account? <a href="{{ route('register') }}">Sign up</a>
    </p>
</div>
@endsection
