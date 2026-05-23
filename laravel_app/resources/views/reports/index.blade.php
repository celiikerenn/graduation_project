@extends('layouts.app')

@section('title', 'Reports')

@push('styles')
<style>
    .reports-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        padding: 1rem 1.15rem;
        background: var(--surface2);
        border: 1px solid var(--border2);
        border-radius: 14px;
    }
    .reports-toolbar__hint {
        margin: 0;
        font-size: 0.85rem;
        color: var(--muted);
        line-height: 1.45;
    }
    .reports-toolbar__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }
    .reports-toolbar__actions .btn {
        white-space: nowrap;
    }
    .reports-toolbar__actions .btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .reports-selection-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 1rem;
        font-size: 0.85rem;
        color: var(--txt2);
    }
    .reports-selection-bar__links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }
    .reports-selection-bar__links button {
        background: none;
        border: none;
        padding: 0;
        font: inherit;
        color: var(--acc);
        font-weight: 600;
        cursor: pointer;
    }
    .reports-selection-bar__links button:hover {
        text-decoration: underline;
    }
    .reports-month-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(13rem, 1fr));
        gap: 1rem;
    }
    @media (min-width: 900px) {
        .reports-month-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (min-width: 1200px) {
        .reports-month-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    .reports-month-card {
        background: var(--surface);
        border: 2px solid var(--border2);
        border-radius: 14px;
        padding: 0;
        display: flex;
        flex-direction: column;
        min-height: 11.5rem;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.07);
        transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease, background 0.15s ease;
        cursor: pointer;
        position: relative;
    }
    .reports-month-card:hover {
        border-color: rgba(37, 99, 235, 0.45);
        box-shadow: 0 10px 28px rgba(37, 99, 235, 0.14);
        transform: translateY(-2px);
    }
    .reports-month-card.is-selected {
        border-color: var(--acc);
        background: linear-gradient(180deg, rgba(37, 99, 235, 0.12) 0%, var(--surface) 55%);
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.18);
    }
    .reports-month-card__head {
        padding: 1rem 1.1rem 0.35rem;
        padding-right: 2.5rem;
        background: linear-gradient(180deg, rgba(37, 99, 235, 0.07) 0%, transparent 100%);
    }
    .reports-month-card.is-selected .reports-month-card__head {
        background: linear-gradient(180deg, rgba(37, 99, 235, 0.14) 0%, transparent 100%);
    }
    .reports-month-card__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--txt);
        line-height: 1.3;
    }
    .reports-month-card__check {
        position: absolute;
        top: 0.85rem;
        right: 0.85rem;
        width: 1.15rem;
        height: 1.15rem;
        accent-color: var(--acc);
        cursor: pointer;
        z-index: 2;
    }
    .reports-month-card__meta {
        padding: 0.35rem 1rem 0.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    .reports-month-card__amount {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--txt);
        font-variant-numeric: tabular-nums;
    }
    .reports-month-card__count {
        margin: 0;
        font-size: 0.8rem;
        color: var(--muted);
    }
    .reports-month-card__actions {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: auto;
        padding: 0 1rem 1rem;
    }
    .reports-month-card__actions .btn {
        min-width: 4.75rem;
        padding: 0.45rem 0.85rem;
        font-size: 0.8rem;
        justify-content: center;
        text-align: center;
        position: relative;
        z-index: 3;
    }
    .reports-empty {
        text-align: center;
        padding: 2.5rem 1.5rem;
    }
    .reports-empty__icon {
        width: 3.5rem;
        height: 3.5rem;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: var(--acc-light);
        color: var(--acc);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<h1>Reports</h1>
<p style="margin-top:-0.35rem; margin-bottom:0.75rem; color:var(--muted); font-size:0.9rem;">
    Download your expenses per month as CSV or PDF, or select several months and grab them in one ZIP.
</p>

@include('partials.ai-insights', ['insights' => $aiInsights ?? []])

@error('reports')
    <div class="text-danger" style="margin-bottom:1rem;">{{ $message }}</div>
@enderror

@if(empty($monthCards))
    <div class="card reports-empty">
        <div class="reports-empty__icon" aria-hidden="true">📊</div>
        <p style="margin:0 0 0.5rem; font-weight:600; color:var(--txt);">No reports yet</p>
        <p style="margin:0; color:var(--muted); font-size:0.9rem;">Add some expenses first, then your monthly exports will appear here.</p>
    </div>
@else
    <div class="reports-toolbar">
        <p class="reports-toolbar__hint" id="reports-selection-summary">Click months to select · selection is kept when you change pages</p>
        <div class="reports-toolbar__actions">
            <button type="button" class="btn btn-primary" id="reports-download-selected-csv" disabled>
                Selected months CSV (ZIP)
            </button>
            <button type="button" class="btn btn-secondary" id="reports-download-selected-pdf" disabled>
                Selected months PDF (ZIP)
            </button>
            <a href="{{ route('reports.download-all-csv') }}" class="btn btn-secondary">
                Download all CSV (ZIP)
            </a>
            <a href="{{ route('reports.download-all-pdf') }}" class="btn btn-secondary">
                Download all PDF (ZIP)
            </a>
        </div>
    </div>

    <div class="card" style="margin-bottom:0;">
        <div class="reports-selection-bar">
            <p style="margin:0; font-weight:600; color:var(--txt); text-transform:uppercase; letter-spacing:0.05em; font-size:0.85rem;">
                Monthly exports
            </p>
            <div class="reports-selection-bar__links">
                <button type="button" id="reports-select-page">Select this page</button>
                <button type="button" id="reports-clear-selection">Clear selection</button>
            </div>
        </div>

        <div class="reports-month-grid" id="reports-month-grid">
            @foreach($monthCards as $card)
                @php
                    [$year, $m] = explode('-', $card['key']);
                @endphp
                <article class="reports-month-card" data-month-key="{{ $card['key'] }}" tabindex="0" role="button" aria-pressed="false">
                    <input
                        type="checkbox"
                        class="reports-month-card__check"
                        value="{{ $card['key'] }}"
                        aria-label="Select {{ $card['label'] }}"
                    >
                    <div class="reports-month-card__head">
                        <h2 class="reports-month-card__title">{{ $card['label'] }}</h2>
                    </div>
                    <div class="reports-month-card__meta">
                        <p class="reports-month-card__amount">
                            {{ number_format($card['total'], 2, '.', ',') }} {{ $currencySymbol }}
                        </p>
                        <p class="reports-month-card__count">
                            {{ $card['count'] }} {{ $card['count'] === 1 ? 'expense' : 'expenses' }}
                        </p>
                    </div>
                    <div class="reports-month-card__actions">
                        <a href="{{ route('reports.csv', ['year' => $year, 'month' => $m]) }}"
                           class="btn btn-primary"
                           data-no-select>
                            CSV
                        </a>
                        <a href="{{ route('reports.pdf', ['year' => $year, 'month' => $m]) }}"
                           class="btn btn-secondary"
                           data-no-select>
                            PDF
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        @if(($totalPages ?? 1) > 1)
            @php
                $current = $page ?? 1;
                $last = $totalPages ?? 1;
            @endphp
            <nav class="app-pagination" aria-label="Reports pagination">
                <p class="app-pagination__info">
                    Page {{ $current }} of {{ $last }} · {{ $totalMonths }} months total
                </p>
                <ul class="app-pagination__links">
                    @if($current > 1)
                        <li>
                            <a href="{{ route('reports.index', ['page' => $current - 1]) }}"
                               class="btn btn-secondary"
                               style="padding:0.25rem 0.6rem; font-size:0.85rem; border-radius:999px;">
                                ‹ Prev
                            </a>
                        </li>
                    @endif
                    @for($p = 1; $p <= $last; $p++)
                        <li>
                            @if($p === $current)
                                <span class="btn btn-primary"
                                      style="padding:0.25rem 0.6rem; font-size:0.85rem; border-radius:999px;">
                                    {{ $p }}
                                </span>
                            @else
                                <a href="{{ route('reports.index', ['page' => $p]) }}"
                                   class="btn btn-secondary"
                                   style="padding:0.25rem 0.6rem; font-size:0.85rem; border-radius:999px;">
                                    {{ $p }}
                                </a>
                            @endif
                        </li>
                    @endfor
                    @if($current < $last)
                        <li>
                            <a href="{{ route('reports.index', ['page' => $current + 1]) }}"
                               class="btn btn-secondary"
                               style="padding:0.25rem 0.6rem; font-size:0.85rem; border-radius:999px;">
                                Next ›
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>

    <form id="reports-download-form" method="POST" action="" style="display:none;">
        @csrf
    </form>
@endif
@endsection

@push('scripts')
@if(!empty($monthCards))
<script>
(function () {
    const STORAGE_KEY = 'fintrack_reports_selected_months';
    const csvUrl = @json(route('reports.download-selected-csv'));
    const pdfUrl = @json(route('reports.download-selected-pdf'));
    const csrf = @json(csrf_token());

    const summaryEl = document.getElementById('reports-selection-summary');
    const csvBtn = document.getElementById('reports-download-selected-csv');
    const pdfBtn = document.getElementById('reports-download-selected-pdf');
    const selectPageBtn = document.getElementById('reports-select-page');
    const clearBtn = document.getElementById('reports-clear-selection');
    const form = document.getElementById('reports-download-form');
    const cards = document.querySelectorAll('.reports-month-card');

    function loadSelected() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed.filter((k) => typeof k === 'string') : [];
        } catch {
            return [];
        }
    }

    function saveSelected(keys) {
        const unique = [...new Set(keys)].sort();
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(unique));
        return unique;
    }

    function syncCard(card, selected) {
        const key = card.dataset.monthKey;
        const checkbox = card.querySelector('.reports-month-card__check');
        const isOn = selected.includes(key);
        checkbox.checked = isOn;
        card.classList.toggle('is-selected', isOn);
        card.setAttribute('aria-pressed', isOn ? 'true' : 'false');
    }

    function refreshUi() {
        const selected = loadSelected();
        cards.forEach((card) => syncCard(card, selected));
        const count = selected.length;
        if (summaryEl) {
            summaryEl.textContent = count === 0
                ? 'Click months to select · selection is kept when you change pages'
                : count + (count === 1 ? ' month selected' : ' months selected');
        }
        const enabled = count > 0;
        if (csvBtn) csvBtn.disabled = !enabled;
        if (pdfBtn) pdfBtn.disabled = !enabled;
        return selected;
    }

    function toggleMonth(key) {
        const selected = loadSelected();
        const idx = selected.indexOf(key);
        if (idx === -1) {
            selected.push(key);
        } else {
            selected.splice(idx, 1);
        }
        saveSelected(selected);
        refreshUi();
    }

    cards.forEach((card) => {
        const checkbox = card.querySelector('.reports-month-card__check');

        checkbox.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMonth(card.dataset.monthKey);
        });

        card.addEventListener('click', (e) => {
            if (e.target.closest('[data-no-select]')) {
                return;
            }
            toggleMonth(card.dataset.monthKey);
        });

        card.addEventListener('keydown', (e) => {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                toggleMonth(card.dataset.monthKey);
            }
        });
    });

    selectPageBtn?.addEventListener('click', () => {
        const selected = loadSelected();
        cards.forEach((card) => {
            const key = card.dataset.monthKey;
            if (!selected.includes(key)) {
                selected.push(key);
            }
        });
        saveSelected(selected);
        refreshUi();
    });

    clearBtn?.addEventListener('click', () => {
        saveSelected([]);
        refreshUi();
    });

    function submitZip(url) {
        const selected = loadSelected();
        if (!selected.length || !form) {
            return;
        }
        form.action = url;
        form.innerHTML = '<input type="hidden" name="_token" value="' + csrf.replace(/"/g, '&quot;') + '">';
        selected.forEach((key) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'months[]';
            input.value = key;
            form.appendChild(input);
        });
        form.submit();
    }

    csvBtn?.addEventListener('click', () => submitZip(csvUrl));
    pdfBtn?.addEventListener('click', () => submitZip(pdfUrl));

    refreshUi();
})();
</script>
@endif
@endpush
