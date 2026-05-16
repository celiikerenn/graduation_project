@extends('layouts.app')

@section('title', 'Analytics')

@push('styles')
<style>
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }
    .chart-card {
        background: var(--surface);
        border-radius: 16px;
        border: 1px solid var(--border);
        box-shadow: 0 1px 3px rgba(15,23,42,0.05), 0 8px 24px rgba(15,23,42,0.06);
        padding: 24px;
    }
    .chart-card h2 {
        margin-top: 0;
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
        color: var(--txt);
    }
    .chart-card canvas {
        max-height: 320px;
    }
    .chart-empty {
        margin-top: 0.5rem;
    }
</style>
@endpush

@section('content')
<h1>Analytics</h1>
<p style="margin-bottom: 0.75rem; color:var(--muted);">
    View charts and spending insights in one place. Use month selector to update category analytics.
</p>

@include('partials.month-filter', [
    'months' => $availableMonths ?? [],
    'selected' => $selectedMonth ?? null,
    'action' => route('charts'),
])

<div class="charts-grid">
    <div class="chart-card" style="grid-column:1 / -1;">
        <h2>Spending Insights</h2>
        <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:12px; margin-bottom:12px;">
            <div style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:12px;">
                <div style="font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em;">Monthly average</div>
                <div class="number-value" style="font-size:20px; font-weight:600;">{{ number_format($monthlyAverage ?? 0, 2, ',', '.') }} {{ $currencySymbol }}</div>
            </div>
            <div style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:12px;">
                <div style="font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em;">Anomaly threshold</div>
                <div class="number-value" style="font-size:20px; font-weight:600;">{{ number_format($anomalyThreshold ?? 0, 2, ',', '.') }} {{ $currencySymbol }}</div>
            </div>
            <div style="background:var(--surface2); border:1px solid var(--border); border-radius:12px; padding:12px;">
                <div style="font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em;">Anomaly months</div>
                <div class="number-value" style="font-size:20px; font-weight:600;">{{ count($anomalyMonths ?? []) }}</div>
            </div>
        </div>
        @if(!empty($insights))
            <ul style="margin:0 0 0.75rem 1rem; padding:0; color:var(--txt2);">
                @foreach($insights as $insight)
                    <li style="margin-bottom:0.35rem;">{{ $insight }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($recommendations))
            <div style="font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.35rem;">Recommendations</div>
            <ul style="margin:0 0 0 1rem; padding:0; color:var(--txt2);">
                @foreach($recommendations as $rec)
                    <li style="margin-bottom:0.25rem;">{{ $rec }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="chart-card">
        <h2>Category Distribution (Pie Chart)</h2>
        @if(count($pieLabels) === 0)
            <div class="empty-state chart-empty" role="status">
                <div class="empty-state__icon" aria-hidden="true">🥧</div>
                <p class="empty-state__title">No categories to chart</p>
                <p class="empty-state__text">Add an expense for this period to see how spending splits by category.</p>
            </div>
        @else
            <p style="font-size:0.9rem; color:var(--muted); margin-top:0; margin-bottom:0.5rem;">
                Each slice shows the percentage share of total spending per category.
            </p>
            <canvas id="pieChart" height="210"></canvas>
        @endif
    </div>

    <div class="chart-card">
        <h2>Category Comparison (Bar Chart)</h2>
        @if(count($barLabels) === 0)
            <div class="empty-state chart-empty" role="status">
                <div class="empty-state__icon" aria-hidden="true">📊</div>
                <p class="empty-state__title">Nothing to compare yet</p>
                <p class="empty-state__text">Once you log expenses by category, bar totals will appear here.</p>
            </div>
        @else
            <p style="font-size:0.9rem; color:var(--muted); margin-top:0; margin-bottom:0.5rem;">
                Bars display the total amount ({{ $currencySymbol }}) spent in each category across all months.
            </p>
            <canvas id="barChart" height="210"></canvas>
        @endif
    </div>

    <div class="chart-card">
        <h2>Monthly Expense Trend (Line Chart)</h2>
        @if(count($lineLabels) < 2)
            <div class="empty-state chart-empty" role="status">
                <div class="empty-state__icon" aria-hidden="true">📈</div>
                <p class="empty-state__title">Trend needs more history</p>
                <p class="empty-state__text">The line chart needs expenses in at least <strong>two different months</strong> to show a trend.</p>
            </div>
        @else
            <canvas id="lineChart" height="210"></canvas>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const pieLabels = @json($pieLabels);
    const pieData = @json($pieData);
    const lineLabels = @json($lineLabels);
    const lineData = @json($lineData);
    const barLabels = @json($barLabels);
    const barData = @json($barData);
    const currencySym = @json($currencySymbol);

    /* Grafik renkleri layouts/app :root --cat-* ile ayni (tek kaynak) */
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

    function sliceOutlineRgba() {
        return 'rgba(15, 23, 42, 0.1)';
    }

    function buildPalette(labels) {
        return labels.map((label, i) => getCategoryColor(label, i));
    }

    function buildBorderArray(len) {
        return Array.from({ length: len }, () => sliceOutlineRgba());
    }

    function gridLineColor() {
        return 'rgba(15, 23, 42, 0.06)';
    }

    function applyChartDefaults() {
        Chart.defaults.color = readCssVar('--muted', '#64748b');
        Chart.defaults.borderColor = gridLineColor();
        Chart.defaults.font.family = 'DM Sans, sans-serif';
    }

    function tickColor() {
        return readCssVar('--muted', '#64748b');
    }

    function legendLabelColor() {
        return readCssVar('--txt2', '#334155');
    }

    window._appChartInstances = window._appChartInstances || [];

    function refreshAllCharts() {
        applyChartDefaults();
        const g = gridLineColor();
        const tc = tickColor();
        const lc = legendLabelColor();
        window._appChartInstances.forEach(function (ch) {
            if (!ch || !ch.options) return;
            if (ch.options.plugins && ch.options.plugins.legend && ch.options.plugins.legend.labels) {
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
            if (ch.config.type === 'pie' && ch.data.datasets[0]) {
                const n = ch.data.datasets[0].borderColor;
                const len = Array.isArray(n) ? n.length : 0;
                if (len) {
                    ch.data.datasets[0].borderColor = Array.from({ length: len }, () => sliceOutlineRgba());
                }
            }
            if (ch.config.type === 'bar' && ch.data.datasets[0]) {
                const n = ch.data.datasets[0].borderColor;
                const len = Array.isArray(n) ? n.length : 0;
                if (len) {
                    ch.data.datasets[0].borderColor = Array.from({ length: len }, () => sliceOutlineRgba());
                }
            }
            ch.update();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyChartDefaults();

        if (pieLabels.length && document.getElementById('pieChart')) {
            const ctxPie = document.getElementById('pieChart').getContext('2d');
            const totalPie = pieData.reduce((sum, v) => sum + Number(v || 0), 0);
            const pieBg = buildPalette(pieLabels);
            const pieChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: pieLabels,
                    datasets: [{
                        data: pieData,
                        backgroundColor: pieBg,
                        borderWidth: 3,
                        borderColor: buildBorderArray(pieLabels.length),
                        hoverBorderWidth: 3,
                    }]
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom', labels: { color: legendLabelColor() } },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = Number(context.raw || 0);
                                    const percent = totalPie > 0 ? (value * 100 / totalPie) : 0;
                                    const percentStr = percent.toLocaleString('tr-TR', { maximumFractionDigits: 1 });
                                    return label + ': ' + percentStr + '%';
                                }
                            }
                        }
                    }
                }
            });
            window._appChartInstances.push(pieChart);
        }

        if (lineLabels.length && document.getElementById('lineChart')) {
            const ctxLine = document.getElementById('lineChart').getContext('2d');
            const lineChart = new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: lineLabels,
                    datasets: [{
                        label: 'Total Amount (' + currencySym + ')',
                        data: lineData,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        pointBackgroundColor: '#3b82f6',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridLineColor() },
                            ticks: { color: tickColor() }
                        },
                        x: {
                            grid: { color: gridLineColor() },
                            ticks: { color: tickColor() }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: legendLabelColor() } }
                    }
                }
            });
            window._appChartInstances.push(lineChart);
        }

        if (barLabels.length && document.getElementById('barChart')) {
            const ctxBar = document.getElementById('barChart').getContext('2d');
            const barBg = buildPalette(barLabels);
            const barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: barLabels,
                    datasets: [{
                        label: 'Total Amount (' + currencySym + ')',
                        data: barData,
                        backgroundColor: barBg,
                        borderColor: buildBorderArray(barLabels.length),
                        borderWidth: 2,
                        borderRadius: 6,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridLineColor() },
                            ticks: { color: tickColor() }
                        },
                        x: {
                            grid: { color: gridLineColor() },
                            ticks: { color: tickColor() }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: legendLabelColor() } }
                    }
                }
            });
            window._appChartInstances.push(barChart);
        }

    });
</script>
@endpush

