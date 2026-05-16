@extends('layouts.app')

@section('title', 'Reports')

@push('styles')
<style>
    .reports-hero {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
        gap: 0.875rem;
        margin-bottom: 1.25rem;
    }
    .reports-stat {
        background: linear-gradient(145deg, var(--surface) 0%, var(--surface2) 100%);
        border: 1px solid var(--border2);
        border-radius: 14px;
        padding: 1rem 1.1rem;
        position: relative;
        overflow: hidden;
    }
    .reports-stat::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #1d4ed8, var(--acc2));
        opacity: 0.85;
    }
    .reports-stat__label {
        margin: 0 0 0.35rem;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--muted);
    }
    .reports-stat__value {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--txt);
        font-variant-numeric: tabular-nums;
        line-height: 1.2;
    }
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
    .reports-toolbar__text {
        margin: 0;
        font-size: 0.9rem;
        color: var(--txt2);
        max-width: 28rem;
        line-height: 1.45;
    }
    .reports-toolbar__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .reports-toolbar__actions .btn {
        white-space: nowrap;
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
        transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
    }
    .reports-month-card:hover {
        border-color: rgba(37, 99, 235, 0.45);
        box-shadow: 0 10px 28px rgba(37, 99, 235, 0.14);
        transform: translateY(-2px);
    }
    .reports-month-card__head {
        padding: 1rem 1.1rem 0.35rem;
        background: linear-gradient(180deg, rgba(37, 99, 235, 0.07) 0%, var(--surface) 100%);
    }
    .reports-month-card__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--txt);
        line-height: 1.3;
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
    .reports-pagination {
        margin-top: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border2);
    }
    .reports-pagination__info {
        margin: 0;
        font-size: 0.875rem;
        color: var(--muted);
    }
    .reports-pagination__links {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
</style>
@endpush

@section('content')
<h1>Reports</h1>
<p style="margin-top:-0.35rem; margin-bottom:1.25rem; color:var(--muted); font-size:0.9rem;">
    Download your expenses per month as CSV or PDF, or grab everything in one ZIP.
</p>

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
    <div class="reports-hero">
        <div class="reports-stat">
            <p class="reports-stat__label">All-time spent</p>
            <p class="reports-stat__value">{{ number_format($grandTotal, 2, '.', ',') }} {{ $currencySymbol }}</p>
        </div>
        <div class="reports-stat">
            <p class="reports-stat__label">All-time budget</p>
            <p class="reports-stat__value">
                @if($allTimeBudget !== null)
                    {{ number_format($allTimeBudget, 2, '.', ',') }} {{ $currencySymbol }}
                @else
                    —
                @endif
            </p>
            @if($allTimeBudget === null)
                <p style="margin:0.35rem 0 0; font-size:0.78rem; color:var(--muted);">
                    <a href="{{ route('profile.show') }}#monthly-budget" style="color:var(--acc); font-weight:600;">Set monthly budget</a>
                </p>
            @endif
        </div>
    </div>

    <div class="reports-toolbar" style="justify-content:center;">
        <div class="reports-toolbar__actions">
            <a href="{{ route('reports.download-all-csv') }}" class="btn btn-primary">
                Download all CSV (ZIP)
            </a>
            <a href="{{ route('reports.download-all-pdf') }}" class="btn btn-secondary">
                Download all PDF (ZIP)
            </a>
        </div>
    </div>

    <div class="card" style="margin-bottom:0;">
        <p style="margin:0 0 1rem; font-size:0.85rem; font-weight:600; color:var(--txt2); text-transform:uppercase; letter-spacing:0.05em;">
            Monthly exports
        </p>
        <div class="reports-month-grid">
            @foreach($monthCards as $card)
                @php
                    [$year, $m] = explode('-', $card['key']);
                @endphp
                <article class="reports-month-card">
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
                           class="btn btn-primary">
                            CSV
                        </a>
                        <a href="{{ route('reports.pdf', ['year' => $year, 'month' => $m]) }}"
                           class="btn btn-secondary">
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
            <nav class="reports-pagination" aria-label="Reports pagination">
                <p class="reports-pagination__info">
                    Page {{ $current }} of {{ $last }} · {{ $totalMonths }} months total
                </p>
                <ul class="reports-pagination__links">
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
@endif
@endsection
