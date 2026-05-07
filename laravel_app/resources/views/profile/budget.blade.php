@extends('layouts.app')

@section('title', 'Edit Budget')

@section('content')
<h1>Edit Monthly Budget</h1>

<div class="card" style="max-width:420px; margin:0 auto;">
    <form method="POST" action="{{ route('profile.update-budget') }}">
        @csrf

        <div class="form-group">
            <label for="monthly_budget">Monthly Budget ({{ $currencySymbol }})</label>
            <input
                id="monthly_budget"
                type="number"
                name="monthly_budget"
                step="0.01"
                min="0"
                value="{{ old('monthly_budget', $monthlyBudget) }}"
            >
            @error('monthly_budget')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.5rem;">
            <button type="submit" class="btn btn-primary">
                Save
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                Back to Dashboard
            </a>
        </div>
    </form>
</div>
@endsection

