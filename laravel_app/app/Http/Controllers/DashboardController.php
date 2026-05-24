<?php

namespace App\Http\Controllers;

use App\Services\AnomalyNotificationService;
use App\Services\FastApiService;
use App\Support\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Dashboard - Bu ay özeti ve hızlı linkler.
 * Veri FastAPI'den alınır; Laravel veritabanına erişmez.
 */
class DashboardController extends Controller
{
    public function __construct(
        protected FastApiService $api,
        protected AnomalyNotificationService $anomalyNotifier,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $now = now();
        $monthly = [];
        $recentMonths = [];
        $monthPage = max((int) $request->query('m_page', 1), 1);
        $perMonthPage = 12;
        $monthTotalPages = 1;
        $monthTotalCount = 0;
        try {
            $monthly = $this->api->getMonthlyTotal($userId, $now->year, $now->month);

            $list = $this->api->listExpenses($userId, 0, 200);
            $expenses = $list['expenses'] ?? [];

            // Tüm aylar için özet (recent months)
            $byMonth = [];
            foreach ($expenses as $expense) {
                if (empty($expense['expense_date'])) {
                    continue;
                }
                $date = Carbon::parse($expense['expense_date']);
                $key = $date->format('Y-m');
                $amount = (float) ($expense['amount'] ?? 0);
                if (!isset($byMonth[$key])) {
                    $byMonth[$key] = [
                        'year'   => $date->year,
                        'month'  => $date->month,
                        'total'  => 0.0,
                        'count'  => 0,
                    ];
                }
                $byMonth[$key]['total'] += $amount;
                $byMonth[$key]['count'] += 1;
            }

            if (!empty($byMonth)) {
                krsort($byMonth); // en yeni aylar önce
                $allMonths = array_values($byMonth);
                $monthTotalCount = count($allMonths);
                $monthTotalPages = max(1, (int) ceil($monthTotalCount / $perMonthPage));
                $monthPage = min($monthPage, $monthTotalPages);
                $offset = ($monthPage - 1) * $perMonthPage;
                $recentMonths = array_slice($allMonths, $offset, $perMonthPage);
            }
        } catch (\Throwable $e) {
            // show empty if API is unreachable
        }

        $userId = (int) $request->session()->get('user_id');
        $userEmail = (string) $request->session()->get('user_email', '');
        $userName = (string) $request->session()->get('user_name', 'User');
        if ($userId > 0 && $userEmail !== '') {
            $this->anomalyNotifier->notifyIfNeeded($userId, $userEmail, $userName);
        }

        return view('dashboard', [
            'userName'         => $request->session()->get('user_name'),
            'monthly'          => $monthly,
            'currentYear'      => $now->year,
            'currentMonth'     => $now->month,
            'recentMonths'     => $recentMonths,
            'monthPage'        => $monthPage,
            'monthTotalPages'  => $monthTotalPages,
            'monthTotalCount'  => $monthTotalCount,
            'perMonthPage'     => $perMonthPage,
        ]);
    }

    /**
     * Gelişmiş grafikler sayfası.
     * Tüm harcamaları FastAPI'den çekip aylık ve kategori bazlı özetler üretir.
     */
    public function charts(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $lineLabels = [];
        $lineData = [];
        $pieLabels = [];
        $pieData = [];
        $barLabels = [];
        $barData = [];
        $availableMonths = [];
        $selectedMonth = null;
        $insights = [];
        $recommendations = [];
        $anomalyMonths = [];
        $monthlyAverage = 0.0;
        $anomalyThreshold = 0.0;
        $monthlyStdDev = 0.0;
        $monthComparison = null;
        $compareMonthA = null;
        $compareMonthB = null;
        $selectedMonthTotal = 0.0;
        $selectedMonthExpenseCount = 0;
        $selectedMonthCategoryCount = 0;
        $selectedMonthLabel = null;
        $now = now();
        try {
            // FastAPI limit üst sınırı 200
            $data = $this->api->listExpenses($userId, 0, 200);
            $expenses = $data['expenses'] ?? [];

            $byMonth = [];
            $byCategory = [];

            foreach ($expenses as $expense) {
                $amount = (float) ($expense['amount'] ?? 0);
                if ($amount <= 0) {
                    continue;
                }

                // Monthly totals
                if (!empty($expense['expense_date'])) {
                    $monthKey = Carbon::parse($expense['expense_date'])->format('Y-m');
                    $byMonth[$monthKey] = ($byMonth[$monthKey] ?? 0) + $amount;
                    $availableMonths[$monthKey] = true;
                }
            }

            // Ay listesi (harcama olan aylar + içinde bulunduğumuz ay)
            $availableMonths = array_keys($availableMonths);
            $currentMonthKey = $now->format('Y-m');
            if (!in_array($currentMonthKey, $availableMonths, true)) {
                $availableMonths[] = $currentMonthKey;
            }
            sort($availableMonths);

            // Seçili ay: ?month=YYYY-MM, yoksa bugünün ayı
            $selectedMonth = $request->query('month');
            if (empty($selectedMonth) || !preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
                $selectedMonth = $currentMonthKey;
            } elseif (!in_array($selectedMonth, $availableMonths, true)) {
                $availableMonths[] = $selectedMonth;
                sort($availableMonths);
            }

            // Pie / bar için sadece seçili ayın kategorileri
            if ($selectedMonth !== null) {
                $byCategory = [];
                foreach ($expenses as $expense) {
                    $amount = (float) ($expense['amount'] ?? 0);
                    if ($amount <= 0 || empty($expense['expense_date'])) {
                        continue;
                    }
                    $monthKey = Carbon::parse($expense['expense_date'])->format('Y-m');
                    if ($monthKey !== $selectedMonth) {
                        continue;
                    }

                    $categoryName = $expense['category_name'] ?? 'Other';
                    $byCategory[$categoryName] = ($byCategory[$categoryName] ?? 0) + $amount;
                    $selectedMonthExpenseCount++;
                }

                $selectedMonthTotal = (float) ($byMonth[$selectedMonth] ?? 0);
                $selectedMonthCategoryCount = count($byCategory);
                $selectedMonthLabel = Carbon::createFromFormat('Y-m', $selectedMonth)->format('F Y');
            }

            // Sort months chronologically
            ksort($byMonth);
            $lineLabels = array_keys($byMonth);
            $lineData = array_values($byMonth);

            // Spending insights (anomaly detection)
            if (!empty($lineData)) {
                $count = count($lineData);
                $monthlyAverage = array_sum($lineData) / $count;

                $variance = 0.0;
                foreach ($lineData as $value) {
                    $variance += pow($value - $monthlyAverage, 2);
                }
                $stdDev = $count > 1 ? sqrt($variance / $count) : 0.0;
                $monthlyStdDev = $stdDev;
                $anomalyThreshold = max($monthlyAverage * 1.5, $monthlyAverage + $stdDev);

                foreach ($byMonth as $monthKey => $total) {
                    if ($total >= $anomalyThreshold) {
                        $anomalyMonths[] = [
                            'month' => $monthKey,
                            'total' => $total,
                            'diff_percent' => $monthlyAverage > 0
                                ? (($total - $monthlyAverage) / $monthlyAverage * 100)
                                : 0,
                        ];
                    }
                }

                if (!empty($anomalyMonths)) {
                    $monthLabels = array_map(fn ($m) => $m['month'], $anomalyMonths);
                    $insights[] = sprintf(
                        'Spending significantly increased in: %s.',
                        implode(', ', $monthLabels)
                    );
                    $insights[] = sprintf(
                        'These months are above the anomaly threshold (%.2f %s).',
                        $anomalyThreshold,
                        Currency::symbol(session('currency'))
                    );
                    $recommendations[] = 'Review category distribution for those months to identify drivers.';
                    $recommendations[] = 'Consider monthly caps for top spending categories.';
                } else {
                    $insights[] = 'No major spending anomaly detected in recent monthly trend.';
                    $recommendations[] = 'Keep monitoring monthly totals to catch sudden spikes early.';
                }
            }

            // Sort categories ascending by total amount for pie chart (smallest slice first)
            asort($byCategory);
            $pieLabels = array_keys($byCategory);
            $pieData = array_values($byCategory);

            // Bar chart uses same (sorted) order
            $barLabels = $pieLabels;
            $barData = $pieData;

            // Compare any two months (?compare_a=YYYY-MM&compare_b=YYYY-MM); omit params = —
            if (!empty($availableMonths)) {
                if (($request->has('compare_a') || $request->has('compare_b'))
                    && !$request->filled('compare_a')
                    && !$request->filled('compare_b')) {
                    $cleanParams = array_filter(
                        ['month' => $request->query('month')],
                        fn ($v) => $v !== null && $v !== ''
                    );

                    return redirect()->route('charts', $cleanParams);
                }

                $compareMonthA = $request->filled('compare_a')
                    && in_array($request->query('compare_a'), $availableMonths, true)
                    ? $request->query('compare_a')
                    : null;
                $hasCompareA = $compareMonthA !== null;

                $compareMonthB = $request->filled('compare_b')
                    && in_array($request->query('compare_b'), $availableMonths, true)
                    ? $request->query('compare_b')
                    : null;
                $hasBaseline = $compareMonthB !== null;

                $totalA = $hasCompareA ? (float) ($byMonth[$compareMonthA] ?? 0) : 0.0;
                $totalB = $hasBaseline ? (float) ($byMonth[$compareMonthB] ?? 0) : 0.0;
                $sameMonth = $hasCompareA && $hasBaseline && $compareMonthA === $compareMonthB;

                $changePercent = null;
                if ($hasCompareA && $hasBaseline && !$sameMonth) {
                    if ($totalB > 0) {
                        $changePercent = (($totalA - $totalB) / $totalB) * 100;
                    } elseif ($totalA > 0) {
                        $changePercent = 100.0;
                    } else {
                        $changePercent = 0.0;
                    }
                }

                $monthComparison = [
                    'a_key'          => $compareMonthA,
                    'a_label'        => $hasCompareA
                        ? Carbon::createFromFormat('Y-m', $compareMonthA)->format('F Y')
                        : null,
                    'a_total'        => $totalA,
                    'b_key'          => $compareMonthB,
                    'b_label'        => $hasBaseline
                        ? Carbon::createFromFormat('Y-m', $compareMonthB)->format('F Y')
                        : null,
                    'b_total'        => $totalB,
                    'has_compare_a'  => $hasCompareA,
                    'has_baseline'   => $hasBaseline,
                    'same_month'     => $sameMonth,
                    'change_percent' => $changePercent,
                ];
            }

        } catch (\Throwable $e) {
            // API ulaşılamazsa boş grafikler gösterilir
        }

        return view('charts', [
            'lineLabels' => $lineLabels,
            'lineData'   => $lineData,
            'pieLabels'  => $pieLabels,
            'pieData'    => $pieData,
            'barLabels'  => $barLabels,
            'barData'    => $barData,
            'availableMonths' => $availableMonths,
            'selectedMonth'   => $selectedMonth,
            'insights'        => $insights,
            'recommendations' => $recommendations,
            'anomalyMonths'   => $anomalyMonths,
            'monthlyAverage'  => $monthlyAverage,
            'monthlyStdDev'   => $monthlyStdDev,
            'anomalyThreshold'=> $anomalyThreshold,
            'monthComparison' => $monthComparison,
            'compareMonthA'   => $compareMonthA,
            'compareMonthB'   => $compareMonthB,
            'selectedMonthTotal' => $selectedMonthTotal,
            'selectedMonthExpenseCount' => $selectedMonthExpenseCount,
            'selectedMonthCategoryCount' => $selectedMonthCategoryCount,
            'selectedMonthLabel' => $selectedMonthLabel,
        ]);
    }
}
