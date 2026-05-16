@php
    $months = $months ?? [];
    $selected = $selected ?? null;
    $action = $action ?? url()->current();
@endphp

@if(count($months) > 0)
<div class="card filter-toolbar">
    <form method="GET" action="{{ $action }}" class="filter-toolbar__form">
        <div class="select-field select-field--compact">
            <select
                id="month"
                name="month"
                class="select-control"
                onchange="this.form.submit()"
                aria-label="Select month"
            >
                @foreach($months as $month)
                    @php
                        try {
                            $monthLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y');
                        } catch (\Throwable) {
                            $monthLabel = $month;
                        }
                    @endphp
                    <option value="{{ $month }}" {{ $selected === $month ? 'selected' : '' }}>
                        {{ $monthLabel }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>
@endif
