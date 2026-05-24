@php
    $months = $months ?? [];
    $selectedMonth = $selectedMonth ?? null;
    $filtersActive = !empty($filtersActive);
@endphp

@if(count($months) > 0)
<div class="card filter-toolbar filter-toolbar--sidebar expenses-month-picker">
    <p class="expenses-filter__heading">Month</p>
    <form method="GET" action="{{ route('expenses.index') }}" class="expenses-filter__form" id="expenses-month-form">
        @foreach($preserveCategoryIds ?? [] as $catId)
            <input type="hidden" name="category_id[]" value="{{ $catId }}">
        @endforeach
        <div class="expenses-filter__field">
            <select id="month" name="month" class="select-control" aria-label="Select month" onchange="this.form.submit()" @if($filtersActive) disabled @endif>
                @if($filtersActive)
                    <option value="" selected>—</option>
                @endif
                @foreach($months as $month)
                    @php
                        try {
                            $monthLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y');
                        } catch (\Throwable) {
                            $monthLabel = $month;
                        }
                    @endphp
                    <option value="{{ $month }}" {{ !$filtersActive && $selectedMonth === $month ? 'selected' : '' }}>
                        {{ $monthLabel }}
                    </option>
                @endforeach
            </select>
        </div>
        @if($filtersActive)
            <p class="expenses-month-picker__note">Date range is active. Clear filters to browse by month.</p>
        @endif
    </form>
</div>
@endif
