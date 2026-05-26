@extends('layouts.app')

@section('title', 'Account Settings')

@push('styles')
@include('profile.partials.settings-subnav-styles')
@endpush

@section('content')
<h1>Settings</h1>
@include('profile.partials.settings-nav')

<div class="card" style="display:flex; flex-wrap:wrap; gap:1.5rem; align-items:flex-start;">
    <div style="flex:1 1 260px; min-width:240px;">
        <h2 style="margin-top:0; margin-bottom:0.75rem;">Account info</h2>
        <div class="form-group">
            <label>Name</label>
            <div>{{ $name }}</div>
        </div>
        <div class="form-group">
            <label>Email</label>
            <div>{{ $email }}</div>
        </div>
    </div>

    <div style="flex:1 1 260px; min-width:260px;">
        <h2 style="margin-top:0; margin-bottom:0.75rem;">Change password</h2>
        <form method="POST" action="{{ route('profile.change-password.update') }}">
            @csrf
            <div class="form-group">
                <label for="current_password">Current password</label>
                <input type="password" id="current_password" name="current_password" required>
                @error('current_password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="new_password">New password</label>
                <input type="password" id="new_password" name="new_password" required>
                @error('new_password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">Confirm new password</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
            </div>

            <button type="submit" class="btn btn-primary">Change password</button>
        </form>
    </div>
</div>
@endsection
