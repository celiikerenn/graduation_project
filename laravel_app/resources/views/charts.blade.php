@extends('layouts.app')

@section('title', 'Analytics')

@push('styles')
<style>
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }
    @media (max-width: 900px) {
        .charts-grid { grid-template-columns: 1fr; }
    }
    .chart-card {
        background: var(--surface);
        border-radius: 16px;
        border: 1px solid var(--border2);
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 12px 32px rgba(15, 23, 42, 0.07);
        padding: 1.35rem 1.4rem 1.25rem;
        position: relative;
        overflow: hidden;
    }
    .chart-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--acc), var(--acc2));
        opacity: 0.9;
    }
    .chart-card--wide { grid-column: 1 / -1; }
    .chart-card__head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }
    .chart-card h2 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--txt);
        letter-spacing: -0.02em;
    }
    .chart-card__subtitle {
        margin: 0 0 0.85rem;
        font-size: 0.82rem;
        color: var(--muted);
        line-height: 1.45;
        max-width: 36rem;
    }
    .chart-wrap {
        position: relative;
        min-height: 260px;
        max-height: 340px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .chart-wrap--tall { min-height: 280px; }
    .chart-wrap canvas {
        width: 100% !important;
        max-height: 320px;
    }
    .chart-empty { margin-top: 0.5rem; }
    .insights-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 700px) {
        .insights-grid { grid-template-columns: 1fr; }
    }
    .insight-stat {
        background: linear-gradient(145deg, var(--surface2) 0%, var(--surface) 100%);
        border: 1px solid var(--border2);
        border-radius: 12px;
        padding: 0.85rem 1rem;
    }
    .insight-stat__label {
        font-size: 0.68rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.07em;
        font-weight: 600;
    }
    .insight-stat__value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--txt);
        margin-top: 0.2rem;
        font-variant-numeric: tabular-nums;
    }

    .insight-stat__hint {
        margin: 0.35rem 0 0;
        font-size: 0.72rem;
        color: var(--muted);
        line-height: 1.4;
    }
    .analytics-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(220px, 272px);
        grid-template-areas:
            "toolbar toolbar"
            "main sidebar";
        gap: 1.25rem;
        align-items: start;
    }
    .analytics-toolbar-span {
        grid-area: toolbar;
        min-width: 0;
    }
    .analytics-main {
        grid-area: main;
        min-width: 0;
    }
    .analytics-sidebar {
        grid-area: sidebar;
        min-width: 0;
    }
    @media (max-width: 1024px) {
        .analytics-layout {
            grid-template-columns: 1fr;
            grid-template-areas:
                "toolbar"
                "sidebar"
                "main";
        }
    }
    @media (min-width: 1025px) {
        .analytics-compare-card {
            position: sticky;
            top: 1.5rem;
        }
        .analytics-main .chart-card:first-child,
        .analytics-sidebar .analytics-compare-card {
            margin-top: 0;
        }
    }
    .charts-month-toolbar {
        margin-bottom: 1.25rem;
    }
    .charts-month-toolbar__form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem 1.25rem;
        width: 100%;
    }
    .charts-month-toolbar__picker {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem 1rem;
        flex-shrink: 0;
    }
    .charts-month-toolbar__label {
        margin: 0;
    }
    .charts-month-toolbar__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem 1rem;
        flex: 1 1 auto;
        justify-content: flex-end;
        min-width: min(100%, 12rem);
    }
    .charts-month-toolbar__stat {
        background: linear-gradient(145deg, var(--surface2) 0%, var(--surface) 100%);
        border: 1px solid var(--border2);
        border-radius: 10px;
        padding: 0.5rem 0.85rem;
        min-width: 5.5rem;
    }
    .charts-month-toolbar__stat-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .charts-month-toolbar__stat-value {
        display: block;
        font-size: 1rem;
        font-weight: 700;
        color: var(--txt);
        margin-top: 0.15rem;
        font-variant-numeric: tabular-nums;
    }
    .charts-month-toolbar__hint {
        flex: 1 1 100%;
        margin: 0;
        font-size: 0.78rem;
        color: var(--muted);
        line-height: 1.4;
    }
    @media (max-width: 700px) {
        .charts-month-toolbar__stats {
            justify-content: flex-start;
        }
    }
    .analytics-compare-card .chart-card__subtitle {
        max-width: none;
        margin-bottom: 1rem;
    }
    .month-compare {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
    }
    .month-compare__box {
        background: linear-gradient(145deg, var(--surface2) 0%, var(--surface) 100%);
        border: 1px solid var(--border2);
        border-radius: 12px;
        padding: 1rem 1.1rem;
    }
    .month-compare__label {
        font-size: 0.72rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 600;
    }
    .month-compare__amount {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--txt);
        margin-top: 0.25rem;
        font-variant-numeric: tabular-nums;
    }
    .month-compare__divider {
        text-align: center;
        font-size: 0.8rem;
        color: var(--muted);
        font-weight: 600;
    }
    .month-compare__badge {
        display: inline-block;
        margin-top: 0.5rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .month-compare__badge--up {
        background: rgba(239, 68, 68, 0.12);
        color: #b91c1c;
    }
    .month-compare__badge--down {
        background: rgba(34, 197, 94, 0.12);
        color: #15803d;
    }
    .month-compare__badge--flat {
        background: var(--surface2);
        color: var(--muted);
    }
    .month-compare-toolbar__form {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
        margin-bottom: 1rem;
    }
    .month-compare-toolbar__field label {
        display: block;
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.35rem;
    }
    .month-compare__divider {
        padding: 0.15rem 0;
    }
    .month-compare__amount {
        font-size: 1.15rem;
    }
    .month-compare-clear {
        width: 100%;
        text-align: center;
        padding: 0.45rem 0.7rem;
        font-size: 0.78rem;
        line-height: 1;
        margin-top: 0.5rem;
    }
</style>
@endpush

@section('content')
<h1>Analytics</h1>
<p style="margin-bottom: 1rem; color: var(--muted); font-size: 0.9rem;">
    Spending trends, category charts, and month-over-month comparison.
</p>

<div class="analytics-layout">
    <div class="analytics-toolbar-span">
        @include('partials.charts-month-toolbar', [
            'months' => $availableMonths ?? [],
            'selected' => $selectedMonth ?? null,
            'action' => route('charts'),
            'hidden' => array_filter([
                'compare_a' => $compareMonthA,
                'compare_b' => $compareMonthB,
            ], fn ($v) => $v !== null && $v !== ''),
            'monthTotal' => $selectedMonthTotal ?? 0,
            'expenseCount' => $selectedMonthExpenseCount ?? 0,
            'categoryCount' => $selectedMonthCategoryCount ?? 0,
            'monthLabel' => $selectedMonthLabel,
        ])
    </div>

    <div class="analytics-main">
        <div class="charts-grid">
    <div class="chart-card chart-card--wide">
        <div class="chart-card__head">
            <h2>Spending insights</h2>
        </div>
        <div class="insights-grid">
            <div class="insight-stat">
                <div class="insight-stat__label">Monthly average</div>
                <div class="insight-stat__value number-value">{{ number_format($monthlyAverage ?? 0, 2, ',', '.') }} {{ $currencySymbol }}</div>
            </div>
            <div class="insight-stat">
                <div class="insight-stat__label">Anomaly threshold</div>
                <div class="insight-stat__value number-value">{{ number_format($anomalyThreshold ?? 0, 2, ',', '.') }} {{ $currencySymbol }}</div>
            </div>
            <div class="insight-stat">
                <div class="insight-stat__label">Anomaly months</div>
                <div class="insight-stat__value number-value">{{ count($anomalyMonths ?? []) }}</div>
            </div>
        </div>
        @if(!empty($insights))
            <ul style="margin:0 0 0.75rem 1rem; padding:0; color:var(--txt2); font-size:0.9rem;">
                @foreach($insights as $insight)
                    <li style="margin-bottom:0.35rem;">{{ $insight }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($recommendations))
            <div style="font-size:0.72rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.07em; font-weight:600; margin-bottom:0.35rem;">Recommendations</div>
            <ul style="margin:0; padding:0 0 0 1rem; color:var(--txt2); font-size:0.9rem;">
                @foreach($recommendations as $rec)
                    <li style="margin-bottom:0.25rem;">{{ $rec }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="chart-card">
        <div class="chart-card__head"><h2>Category distribution</h2></div>
        @if(count($pieLabels) === 0)
            <div class="empty-state chart-empty" role="status">
                <div class="empty-state__icon" aria-hidden="true">🥧</div>
                <p class="empty-state__title">No categories to chart</p>
                <p class="empty-state__text">Add an expense for this period to see how spending splits by category.</p>
            </div>
        @else
            <p class="chart-card__subtitle">Share of total spending per category for the selected period.</p>
            <div class="chart-wrap"><canvas id="pieChart"></canvas></div>
        @endif
    </div>

    <div class="chart-card">
        <div class="chart-card__head"><h2>Category comparison</h2></div>
        @if(count($barLabels) === 0)
            <div class="empty-state chart-empty" role="status">
                <div class="empty-state__icon" aria-hidden="true">📊</div>
                <p class="empty-state__title">Nothing to compare yet</p>
                <p class="empty-state__text">Once you log expenses by category, bar totals will appear here.</p>
            </div>
        @else
            <p class="chart-card__subtitle">Total spent ({{ $currencySymbol }}) per category for the selected month.</p>
            <div class="chart-wrap chart-wrap--tall"><canvas id="barChart"></canvas></div>
        @endif
    </div>

    <div class="chart-card chart-card--wide">
        <div class="chart-card__head"><h2>Monthly expense trend</h2></div>
        @if(count($lineLabels) < 2)
            <div class="empty-state chart-empty" role="status">
                <div class="empty-state__icon" aria-hidden="true">📈</div>
                <p class="empty-state__title">Trend needs more history</p>
                <p class="empty-state__text">The line chart needs expenses in at least <strong>two different months</strong> to show a trend.</p>
            </div>
        @else
            <p class="chart-card__subtitle">How your total spending changes month over month.</p>
            <div class="chart-wrap chart-wrap--tall"><canvas id="lineChart"></canvas></div>
        @endif
    </div>
        </div>
    </div>

    <aside class="analytics-sidebar" aria-label="Month comparison">
        @if(!empty($monthComparison) && count($availableMonths ?? []) > 0)
            @php
                $cmp = $monthComparison;
                $pct = $cmp['change_percent'];
                $badgeClass = 'month-compare__badge--flat';
                $badgeText = '0%';
                if (($cmp['has_compare_a'] ?? false) && ($cmp['has_baseline'] ?? false) && !$cmp['same_month'] && $pct !== null) {
                    if ($pct > 0.05) {
                        $badgeClass = 'month-compare__badge--up';
                        $badgeText = '+' . number_format($pct, 1, ',', '.') . '%';
                    } elseif ($pct < -0.05) {
                        $badgeClass = 'month-compare__badge--down';
                        $badgeText = number_format($pct, 1, ',', '.') . '%';
                    } else {
                        $badgeText = '0%';
                    }
                }
                $clearCompareParams = array_filter(
                    ['month' => $selectedMonth ?? null],
                    fn ($v) => $v !== null && $v !== ''
                );
            @endphp
            <div class="chart-card analytics-compare-card">
                <div class="chart-card__head"><h2>Compare months</h2></div>
                <p class="chart-card__subtitle">Month A vs Month B.</p>

                <form method="GET" action="{{ route('charts') }}" class="month-compare-toolbar__form">
                    @if(!empty($selectedMonth))
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    @endif
                    <div class="month-compare-toolbar__field">
                        <label for="compare_a">Month A</label>
                        <div class="select-field select-field--compact">
                            <select id="compare_a" name="compare_a" class="select-control" onchange="this.form.submit()">
                                <option value="" @selected(!$compareMonthA)>—</option>
                                @foreach($availableMonths as $m)
                                    @php
                                        try {
                                            $mLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m', $m)->format('F Y');
                                        } catch (\Throwable) {
                                            $mLabel = $m;
                                        }
                                    @endphp
                                    <option value="{{ $m }}" @selected($compareMonthA === $m)>{{ $mLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="month-compare-toolbar__field">
                        <label for="compare_b">Month B</label>
                        <div class="select-field select-field--compact">
                            <select id="compare_b" name="compare_b" class="select-control" onchange="this.form.submit()">
                                <option value="" @selected(!$compareMonthB)>—</option>
                                @foreach($availableMonths as $m)
                                    @php
                                        try {
                                            $mLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m', $m)->format('F Y');
                                        } catch (\Throwable) {
                                            $mLabel = $m;
                                        }
                                    @endphp
                                    <option value="{{ $m }}" @selected($compareMonthB === $m)>{{ $mLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <a href="{{ route('charts', $clearCompareParams) }}" class="btn btn-secondary month-compare-clear">Clear</a>
                    </div>
                </form>

                <div class="month-compare">
                    @if($cmp['has_compare_a'] ?? false)
                        <div class="month-compare__box">
                            <div class="month-compare__label">{{ $cmp['a_label'] }}</div>
                            <div class="month-compare__amount number-value">{{ number_format($cmp['a_total'], 2, ',', '.') }} {{ $currencySymbol }}</div>
                        </div>
                    @endif
                    @if(($cmp['has_compare_a'] ?? false) && ($cmp['has_baseline'] ?? false))
                        <div class="month-compare__divider">vs</div>
                    @endif
                    @if($cmp['has_baseline'] ?? false)
                        <div class="month-compare__box">
                            <div class="month-compare__label">{{ $cmp['b_label'] }}</div>
                            <div class="month-compare__amount number-value">{{ number_format($cmp['b_total'], 2, ',', '.') }} {{ $currencySymbol }}</div>
                        </div>
                    @endif
                </div>
                @if($cmp['same_month'])
                    <p style="margin:0.5rem 0 0;font-size:0.82rem;color:var(--muted);">Pick two different months for % change.</p>
                @elseif(($cmp['has_compare_a'] ?? false) && ($cmp['has_baseline'] ?? false) && $pct !== null)
                    <span class="month-compare__badge {{ $badgeClass }}">{{ $badgeText }}</span>
                @elseif(!($cmp['has_compare_a'] ?? false) && !($cmp['has_baseline'] ?? false))
                    <p style="margin:0.5rem 0 0;font-size:0.82rem;color:var(--muted);">Select months above to compare totals.</p>
                @endif
            </div>
        @endif
    </aside>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
(function () {
    const pieLabels = @json($pieLabels);
    const pieData = @json($pieData);
    const lineLabels = @json($lineLabels);
    const lineData = @json($lineData);
    const barLabels = @json($barLabels);
    const barData = @json($barData);
    const currencySym = @json($currencySymbol);

    function readCssVar(name, fallback) {
        const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        return v || fallback;
    }

    const categoryColors = {
        food: readCssVar('--cat-food', '#f59e0b'),
        transport: readCssVar('--cat-transport', '#3b82f6'),
        utilities: readCssVar('--cat-utilities', '#8b5cf6'),
        grocery: readCssVar('--cat-grocery', '#10b981'),
        groceries: readCssVar('--cat-groceries', '#10b981'),
        health: readCssVar('--cat-health', '#ec4899'),
        entertainment: readCssVar('--cat-entertainment', '#f97316'),
        education: readCssVar('--cat-education', '#06b6d4'),
        clothing: readCssVar('--cat-clothing', '#a78bfa'),
        rent: readCssVar('--cat-rent', '#ef4444'),
        other: readCssVar('--cat-other', '#6b7280'),
    };

    const fallbackColors = [
        categoryColors.food,
        categoryColors.transport,
        categoryColors.health,
        categoryColors.clothing,
        categoryColors.utilities,
        categoryColors.entertainment,
        categoryColors.education,
        categoryColors.other,
        readCssVar('--cat-fb-0', '#F472B6'),
        readCssVar('--cat-fb-1', '#84CC16'),
    ];

    function normalizeText(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/ı/g, 'i');
    }

    function getCategoryColor(label, index) {
        const key = normalizeText(label);
        for (const token in categoryColors) {
            if (key.includes(token)) {
                return categoryColors[token];
            }
        }
        return fallbackColors[index % fallbackColors.length];
    }

    function hexToRgba(hex, alpha) {
        const h = String(hex).trim();
        if (h.startsWith('rgba') || h.startsWith('rgb')) {
            return h;
        }
        let c = h.replace('#', '');
        if (c.length === 3) {
            c = c.split('').map((x) => x + x).join('');
        }
        const n = parseInt(c, 16);
        const r = (n >> 16) & 255;
        const g = (n >> 8) & 255;
        const b = n & 255;
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    function buildPalette(labels) {
        return labels.map((label, i) => getCategoryColor(label, i));
    }

    function sliceBorderColors(len) {
        return Array.from({ length: len }, () => readCssVar('--surface', '#ffffff'));
    }

    function gridLineColor() {
        return 'rgba(15, 23, 42, 0.06)';
    }

    function tickColor() {
        return readCssVar('--muted', '#64748b');
    }

    function legendLabelColor() {
        return readCssVar('--txt2', '#334155');
    }

    function modernTooltip() {
        return {
            backgroundColor: 'rgba(15, 23, 42, 0.92)',
            titleColor: '#f8fafc',
            bodyColor: '#e2e8f0',
            borderColor: 'rgba(148, 163, 184, 0.25)',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 10,
            titleFont: { size: 13, weight: '600', family: 'DM Sans, sans-serif' },
            bodyFont: { size: 12, family: 'DM Sans, sans-serif' },
            displayColors: true,
            boxPadding: 6,
            usePointStyle: true,
        };
    }

    function modernLegend() {
        return {
            position: 'bottom',
            labels: {
                color: legendLabelColor(),
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 18,
                font: { size: 12, weight: '500', family: 'DM Sans, sans-serif' },
            },
        };
    }

    function modernScales() {
        const g = gridLineColor();
        const tc = tickColor();
        return {
            x: {
                grid: { display: false, drawBorder: false },
                ticks: { color: tc, font: { size: 11, weight: '500' }, maxRotation: 45, minRotation: 0 },
                border: { display: false },
            },
            y: {
                beginAtZero: true,
                grid: { color: g, drawBorder: false },
                ticks: { color: tc, font: { size: 11 }, padding: 8 },
                border: { display: false },
            },
        };
    }

    function barGradients(ctx, labels, chartHeight) {
        return labels.map((label, i) => {
            const base = getCategoryColor(label, i);
            const g = ctx.createLinearGradient(0, chartHeight, 0, 0);
            g.addColorStop(0, hexToRgba(base, 0.55));
            g.addColorStop(1, hexToRgba(base, 1));
            return g;
        });
    }

    function applyChartDefaults() {
        Chart.defaults.color = tickColor();
        Chart.defaults.borderColor = gridLineColor();
        Chart.defaults.font.family = 'DM Sans, sans-serif';
        Chart.defaults.animation.duration = 800;
        Chart.defaults.animation.easing = 'easeOutQuart';
    }

    window._appChartInstances = window._appChartInstances || [];

    function refreshAllCharts() {
        applyChartDefaults();
        const g = gridLineColor();
        const tc = tickColor();
        const lc = legendLabelColor();
        window._appChartInstances.forEach(function (ch) {
            if (!ch || !ch.options) return;
            if (ch.options.plugins?.legend?.labels) {
                ch.options.plugins.legend.labels.color = lc;
            }
            if (ch.options.scales) {
                ['x', 'y'].forEach(function (axis) {
                    const sc = ch.options.scales[axis];
                    if (!sc) return;
                    if (sc.grid) sc.grid.color = g;
                    if (sc.ticks) sc.ticks.color = tc;
                });
            }
            ch.update();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyChartDefaults();

        if (pieLabels.length && document.getElementById('pieChart')) {
            const canvas = document.getElementById('pieChart');
            const ctxPie = canvas.getContext('2d');
            const totalPie = pieData.reduce((sum, v) => sum + Number(v || 0), 0);
            const pieBg = buildPalette(pieLabels);

            const pieChart = new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: pieBg,
                        borderWidth: 3,
                        borderColor: sliceBorderColors(pieLabels.length),
                        hoverBorderWidth: 3,
                        hoverOffset: 10,
                        borderRadius: 6,
                        spacing: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    layout: { padding: 8 },
                    plugins: {
                        legend: modernLegend(),
                        tooltip: {
                            ...modernTooltip(),
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = Number(context.raw || 0);
                                    const percent = totalPie > 0 ? (value * 100 / totalPie) : 0;
                                    const percentStr = percent.toLocaleString('tr-TR', { maximumFractionDigits: 1 });
                                    const amt = value.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    return label + ': ' + percentStr + '% (' + amt + ' ' + currencySym + ')';
                                },
                            },
                        },
                    },
                },
            });
            window._appChartInstances.push(pieChart);
        }

        if (lineLabels.length && document.getElementById('lineChart')) {
            const canvas = document.getElementById('lineChart');
            const ctxLine = canvas.getContext('2d');
            const h = canvas.parentElement?.clientHeight || 280;
            const acc = readCssVar('--acc', '#2563eb');
            const acc2 = readCssVar('--acc2', '#3b82f6');
            const fillGrad = ctxLine.createLinearGradient(0, 0, 0, h);
            fillGrad.addColorStop(0, hexToRgba(acc, 0.28));
            fillGrad.addColorStop(0.55, hexToRgba(acc2, 0.08));
            fillGrad.addColorStop(1, hexToRgba(acc, 0));

            const lineChart = new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: lineLabels,
                    datasets: [{
                        label: 'Total (' + currencySym + ')',
                        data: lineData,
                        borderColor: acc,
                        backgroundColor: fillGrad,
                        pointBackgroundColor: readCssVar('--surface', '#fff'),
                        pointBorderColor: acc,
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointHoverBackgroundColor: acc,
                        pointHoverBorderColor: '#fff',
                        borderWidth: 2.5,
                        tension: 0.38,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    scales: modernScales(),
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...modernTooltip(),
                            callbacks: {
                                label: function (ctx) {
                                    const v = Number(ctx.raw || 0);
                                    return ' ' + v.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + currencySym;
                                },
                            },
                        },
                    },
                },
            });
            window._appChartInstances.push(lineChart);
        }

        if (barLabels.length && document.getElementById('barChart')) {
            const canvas = document.getElementById('barChart');
            const ctxBar = canvas.getContext('2d');
            const chartH = canvas.parentElement?.clientHeight || 280;
            const barBg = barGradients(ctxBar, barLabels, chartH);

            const barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: 'Total (' + currencySym + ')',
                        data: barData,
                        backgroundColor: barBg,
                        borderColor: 'transparent',
                        borderWidth: 0,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 36,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: modernScales(),
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            ...modernTooltip(),
                            callbacks: {
                                label: function (ctx) {
                                    const v = Number(ctx.raw || 0);
                                    return ' ' + v.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + currencySym;
                                },
                            },
                        },
                    },
                },
            });
            window._appChartInstances.push(barChart);
        }
    });
})();
</script>
@endpush
