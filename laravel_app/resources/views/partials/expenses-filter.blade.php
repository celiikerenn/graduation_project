@php
    $filters = $filters ?? [];
    $categories = $categories ?? [];
    $defaultMonth = $defaultMonth ?? null;
    $dateFrom = $filters['date_from'] ?? '';
    $dateTo = $filters['date_to'] ?? '';
    $categoryId = $filters['category_id'] ?? '';
@endphp

<div class="card filter-toolbar filter-toolbar--expenses filter-toolbar--sidebar expenses-filters-panel">
    <p class="expenses-filter__heading">Filters</p>
    <form method="GET" action="{{ route('expenses.index') }}" class="expenses-filter__form" id="expenses-filter-form">
        <div class="expenses-filter__field">
            <label for="date_from">From</label>
            @include('partials.date-input', [
                'id' => 'date_from',
                'name' => 'date_from',
                'value' => $dateFrom,
                'max' => date('Y-m-d'),
                'inputClass' => 'select-control',
            ])
        </div>
        <div class="expenses-filter__field">
            <label for="date_to">To</label>
            @include('partials.date-input', [
                'id' => 'date_to',
                'name' => 'date_to',
                'value' => $dateTo,
                'max' => date('Y-m-d'),
                'inputClass' => 'select-control',
            ])
        </div>
        <div class="expenses-filter__field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" class="select-control select-enhanced">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat['id'] }}" {{ (string) $categoryId === (string) $cat['id'] ? 'selected' : '' }}>
                        {{ $cat['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="expenses-filter__actions">
            <button type="submit" class="btn btn-primary">Apply</button>
            <a href="{{ route('expenses.index', array_filter(['month' => $defaultMonth])) }}" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>
