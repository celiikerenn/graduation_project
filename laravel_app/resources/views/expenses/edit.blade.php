@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
<h1>Edit Expense</h1>
<div class="card">
    <form method="POST" action="{{ route('expenses.update', $expense['id']) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat['id'] }}"
                        {{ old('category_id', $expense['category_id']) == $cat['id'] ? 'selected' : '' }}>
                        {{ $cat['name'] }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="amount">Amount ({{ $currencySymbol }})</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                   value="{{ old('amount', $expense['amount']) }}" required>
            @error('amount') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="expense_date">Expense Date</label>
            <input type="date" id="expense_date" name="expense_date"
                   max="{{ date('Y-m-d') }}"
                   value="{{ old('expense_date', $expense['expense_date']) }}" required>
            @error('expense_date') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="description">Description (optional)</label>
            <textarea id="description" name="description" rows="3">{{ old('description', $expense['description'] ?? '') }}</textarea>
            @error('description') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection

