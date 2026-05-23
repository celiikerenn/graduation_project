@php
    $months = $months ?? [];
    $selected = $selected ?? null;
    $action = $action ?? url()->current();
    $hidden = $hidden ?? [];
    $total = (float) ($monthTotal ?? 0);
    $expenseCount = (int) ($expenseCount ?? 0);
    $categoryCount = (int) ($categoryCount ?? 0);
    $monthLabel = $monthLabel ?? null;
@endphp

@if(count($months) > 0)
<div class="card filter-toolbar charts-month-toolbar">
    <form method="GET" action="{{ $action }}" class="filter-toolbar__form charts-month-toolbar__form">
        @foreach($hidden as $name => $value)
            @if($value !== null && $value !== '')
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="charts-month-toolbar__picker">
            <span class="filter-toolbar__label charts-month-toolbar__label">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Chart month
            </span>
            <div class="select-field select-field--compact">
                <select
                    id="month"
                    name="month"
                    class="select-control"
                    onchange="this.form.submit()"
                    aria-label="Select month for category charts"
                >
                    @foreach($months as $month)
                        @php
                            try {
                                $optionLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y');
                            } catch (\Throwable) {
                                $optionLabel = $month;
                            }
                        @endphp
                        <option value="{{ $month }}" @selected($selected === $month)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($selected !== null)
            <div class="charts-month-toolbar__stats" aria-live="polite">
                <div class="charts-month-toolbar__stat">
                    <span class="charts-month-toolbar__stat-label">Total spent</span>
                    <span class="charts-month-toolbar__stat-value number-value">{{ number_format($total, 2, ',', '.') }} {{ $currencySymbol ?? '₺' }}</span>
                </div>
                <div class="charts-month-toolbar__stat">
                    <span class="charts-month-toolbar__stat-label">Expenses</span>
                    <span class="charts-month-toolbar__stat-value number-value">{{ $expenseCount }}</span>
                </div>
                <div class="charts-month-toolbar__stat">
                    <span class="charts-month-toolbar__stat-label">Categories</span>
                    <span class="charts-month-toolbar__stat-value number-value">{{ $categoryCount }}</span>
                </div>
            </div>
            <p class="charts-month-toolbar__hint">Pie and bar charts use data for {{ $monthLabel ?? 'this month' }}.</p>
        @endif
    </form>
</div>
@endif
