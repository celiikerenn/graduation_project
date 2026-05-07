<?php

namespace App\Http\Controllers;

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
        protected FastApiService $api
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
                $totalMonths = count($allMonths);
                $totalPages = (int) ceil($totalMonths / $perMonthPage);
                $monthPage = min($monthPage, max($totalPages, 1));
                $offset = ($monthPage - 1) * $perMonthPage;
                $recentMonths = array_slice($allMonths, $offset, $perMonthPage);
            }
        } catch (\Throwable $e) {
            // show empty if API is unreachable
        }

        return view('dashboard', [
            'userName'       => $request->session()->get('user_name'),
            'monthly'        => $monthly,
            'currentYear'    => $now->year,
            'currentMonth'   => $now->month,
            'recentMonths'   => $recentMonths,
            'monthPage'      => $monthPage,
            'perMonthPage'   => $perMonthPage,
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

            // Ay listesi (harcama olan aylar)
            $availableMonths = array_keys($availableMonths);
            sort($availableMonths);

            // Seçili ay: ?month=YYYY-MM, yoksa en son ay
            $selectedMonth = $request->query('month');
            if (empty($selectedMonth) || !in_array($selectedMonth, $availableMonths, true)) {
                $selectedMonth = !empty($availableMonths) ? end($availableMonths) : null;
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
                }
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
            'anomalyThreshold'=> $anomalyThreshold,
        ]);
    }
}
