@extends('layouts.app')

@section('title', 'Profile & Settings')

@push('styles')
<style>
    .settings-layout {
        display: grid;
        grid-template-columns: minmax(260px, 320px) minmax(0, 1fr);
        gap: 1rem;
        margin-top: 1rem;
        align-items: start;
    }
    .settings-layout__stack {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        min-width: 0;
    }
    .settings-panel h2 {
        margin-top: 0;
        margin-bottom: 0.75rem;
        font-size: 1.05rem;
        color: var(--txt);
    }
    .settings-panel .form-group > div:not(.text-danger) {
        color: var(--txt);
        font-weight: 500;
        font-size: 0.95rem;
    }
    .settings-panel__hint {
        margin-top: 0;
        margin-bottom: 0.85rem;
        color: var(--txt2);
        font-size: 0.9rem;
        font-weight: 500;
        line-height: 1.45;
    }
    .settings-panel--fixed {
        min-height: 100%;
    }
    .settings-panel--fixed h2 {
        font-size: 1.15rem;
    }
    .fixed-template-row {
        display: grid;
        grid-template-columns: 1.15fr 0.95fr 1.35fr auto;
        gap: 0.65rem;
        margin-bottom: 0.7rem;
    }
    .settings-panel--fixed .fixed-template-row {
        grid-template-columns: 1.2fr 1fr 1.5fr auto;
        gap: 0.75rem;
        margin-bottom: 0.8rem;
    }
    .fixed-template-row select,
    .fixed-template-row input {
        background: var(--surface2);
        border: 1.5px solid var(--border2);
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 14px;
        color: var(--txt);
        width: 100%;
        font-family: 'DM Sans', sans-serif;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }
    .settings-panel--fixed .fixed-template-row select,
    .settings-panel--fixed .fixed-template-row input {
        padding: 12px 14px;
        font-size: 15px;
        border-radius: 11px;
    }
    .fixed-template-row select:focus,
    .fixed-template-row input:focus {
        outline: none;
        border-color: var(--acc);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
    }
    .fixed-template-remove {
        min-width: 2.35rem;
        min-height: 2.35rem;
        padding: 0;
        border-radius: 10px;
        border: 1px solid rgba(220, 38, 38, 0.25);
        background: var(--red-light);
        color: var(--red);
        cursor: pointer;
        font-weight: 700;
        font-size: 1.1rem;
        line-height: 1;
    }
    .settings-panel--fixed .fixed-template-remove {
        min-width: 2.5rem;
        min-height: 2.5rem;
        font-size: 1.2rem;
    }
    .fixed-template-remove:hover {
        background: rgba(220, 38, 38, 0.16);
        border-color: rgba(220, 38, 38, 0.35);
    }
    @media (max-width: 900px) {
        .settings-layout {
            grid-template-columns: 1fr;
        }
        .fixed-template-row,
        .settings-panel--fixed .fixed-template-row {
            grid-template-columns: 1fr 1fr;
        }
        .fixed-template-row .fixed-template-remove {
            grid-column: 2;
            justify-self: end;
        }
    }
</style>
@endpush

@section('content')
<h1>Profile &amp; Settings</h1>

<div class="card" style="display:flex; flex-wrap:wrap; gap:1.5rem; align-items:flex-start;">
    <div style="flex:1 1 260px; min-width:240px;">
        <h2 style="margin-top:0; margin-bottom:0.75rem;">Account Info</h2>
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
        <h2 style="margin-top:0; margin-bottom:0.75rem;">Change Password</h2>
        <form method="POST" action="{{ route('profile.change-password.update') }}">
            @csrf
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    required
                >
                @error('current_password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    required
                >
                @error('new_password')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="new_password_confirmation">Confirm New Password</label>
                <input
                    type="password"
                    id="new_password_confirmation"
                    name="new_password_confirmation"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">
                Change Password
            </button>
        </form>
    </div>
</div>

<div class="settings-layout">
    <div class="settings-layout__stack">
        <div class="card settings-panel" id="monthly-budget">
            <h2>Monthly Budget</h2>
            <p class="settings-panel__hint">
                Used on the dashboard for budget usage tracking.
            </p>
            <form method="POST" action="{{ route('profile.update-budget') }}">
                @csrf
                <div class="form-group">
                    <label for="monthly_budget">Amount ({{ $currencySymbol }})</label>
                    <input
                        type="number"
                        id="monthly_budget"
                        name="monthly_budget"
                        step="0.01"
                        min="0"
                        value="{{ old('monthly_budget', $monthlyBudget ?? 0) }}"
                    >
                    @error('monthly_budget')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Save budget</button>
            </form>
        </div>

        <div class="card settings-panel">
            <h2>Preferences</h2>
            <form method="POST" action="{{ route('profile.currency.update') }}">
                @csrf
                <div class="form-group">
                    <label for="currency">Currency</label>
                    <select id="currency" name="currency" class="select-control" required>
                        <option value="TRY" {{ ($currency ?? 'TRY') === 'TRY' ? 'selected' : '' }}>TRY (₺)</option>
                        <option value="USD" {{ ($currency ?? '') === 'USD' ? 'selected' : '' }}>USD ($)</option>
                        <option value="EUR" {{ ($currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        <option value="GBP" {{ ($currency ?? '') === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>

    <div class="card settings-panel settings-panel--fixed">
        <h2>Monthly Fixed Expenses</h2>
        <p class="settings-panel__hint">
            Template rows for the "Add this month's fixed expenses" button on Add Expense.
        </p>
        <form method="POST" action="{{ route('profile.fixed-monthly.templates.update') }}">
        @csrf
        @php($rows = old('templates', $fixedTemplates ?? []))
        <div id="fixed-template-rows">
            @foreach($rows as $i => $row)
                <div class="fixed-template-row" data-fixed-row>
                    <select name="templates[{{ $i }}][category]" class="select-enhanced">
                        <option value="">Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat['name'] }}" {{ ($row['category'] ?? '') === $cat['name'] ? 'selected' : '' }}>{{ $cat['name'] }}</option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" min="0.01" name="templates[{{ $i }}][amount]" value="{{ $row['amount'] ?? '' }}" placeholder="Amount">
                    <input type="text" name="templates[{{ $i }}][description]" value="{{ $row['description'] ?? '' }}" placeholder="Description">
                    <button type="button" class="fixed-template-remove" data-fixed-remove title="Remove row">×</button>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-fixed-template-row" class="btn btn-secondary" style="margin-bottom:0.65rem;">+ Add Row</button>
        @error('amount')
            <div class="text-danger" style="margin-bottom:0.5rem;">{{ $message }}</div>
        @enderror
        <button type="submit" class="btn btn-primary">Save templates</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const rowsWrap = document.getElementById('fixed-template-rows');
    const addBtn = document.getElementById('add-fixed-template-row');
    if (!rowsWrap || !addBtn) return;

    function reindexRows() {
        const rows = Array.from(rowsWrap.querySelectorAll('[data-fixed-row]'));
        rows.forEach((row, idx) => {
            row.querySelectorAll('select, input').forEach((field) => {
                const name = field.getAttribute('name') || '';
                field.setAttribute('name', name.replace(/templates\[\d+\]/, `templates[${idx}]`));
            });
        });
    }

    function categoryOptionsHtml() {
        return `
            <option value="">Category</option>
            @foreach($categories as $cat)
                <option value="{{ $cat['name'] }}">{{ $cat['name'] }}</option>
            @endforeach
        `;
    }

    function createRow() {
        const row = document.createElement('div');
        row.className = 'fixed-template-row';
        row.setAttribute('data-fixed-row', '1');
        row.innerHTML = `
            <select name="templates[0][category]" class="select-enhanced">
                ${categoryOptionsHtml()}
            </select>
            <input type="number" step="0.01" min="0.01" name="templates[0][amount]" placeholder="Amount">
            <input type="text" name="templates[0][description]" placeholder="Description">
            <button type="button" class="fixed-template-remove" data-fixed-remove title="Remove row">×</button>
        `;
        return row;
    }

    addBtn.addEventListener('click', () => {
        const row = createRow();
        rowsWrap.appendChild(row);
        reindexRows();
        if (typeof window.initAppSelects === 'function') {
            window.initAppSelects(row);
        }
    });

    rowsWrap.addEventListener('click', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (!target.matches('[data-fixed-remove]')) return;
        const row = target.closest('[data-fixed-row]');
        if (!row) return;
        const select = row.querySelector('select');
        if (select?.tomselect) {
            select.tomselect.destroy();
        }
        row.remove();
        reindexRows();
    });

    reindexRows();
    if (typeof window.initAppSelects === 'function') {
        window.initAppSelects(rowsWrap);
    }
})();
</script>
@endpush

