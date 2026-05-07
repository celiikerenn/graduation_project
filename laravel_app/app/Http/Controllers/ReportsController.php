<?php

namespace App\Http\Controllers;

use App\Services\FastApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportsController extends Controller
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

        $months = $this->getExpenseMonths($userId);

        return view('reports.index', [
            'months' => $months,
        ]);
    }

    public function downloadCsv(Request $request, int $year, int $month): Response|RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $key = sprintf('%04d-%02d', $year, $month);
        $months = $this->getExpenseMonths($userId);
        if (!in_array($key, $months, true)) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'No expenses found for the selected month.']);
        }

        $expenses = $this->fetchMonthExpenses($userId, $year, $month);

        $totalAmount = 0.0;
        foreach ($expenses as $expense) {
            $totalAmount += (float)($expense['amount'] ?? 0);
        }

        $rows = [];
        $rows[] = ['Date', 'Category', 'Description', 'Amount'];

        foreach ($expenses as $expense) {
            $date = Carbon::parse($expense['expense_date']);
            $rows[] = [
                $date->format('Y-m-d'),
                (string) ($expense['category_name'] ?? ''),
                (string) ($expense['description'] ?? ''),
                (string) ($expense['amount'] ?? '0'),
            ];
        }

        // Toplam satırı
        $rows[] = [
            '',
            '',
            'Total',
            (string) $totalAmount,
        ];

        $csvLines = [];
        foreach ($rows as $row) {
            $escaped = array_map(function (string $field): string {
                $field = str_replace('"', '""', $field);
                return '"' . $field . '"';
            }, $row);
            $csvLines[] = implode(',', $escaped);
        }
        $csvContent = implode("\n", $csvLines);

        $filename = sprintf('expenses_%d_%02d.csv', $year, $month);

        return response($csvContent, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function downloadPdf(Request $request, int $year, int $month): RedirectResponse|Response
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $key = sprintf('%04d-%02d', $year, $month);
        $months = $this->getExpenseMonths($userId);
        if (!in_array($key, $months, true)) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'No expenses found for the selected month.']);
        }

        $expenses = $this->fetchMonthExpenses($userId, $year, $month);

        $totalAmount = 0.0;
        foreach ($expenses as $expense) {
            $totalAmount += (float)($expense['amount'] ?? 0);
        }

        $pdf = Pdf::loadView('reports.pdf', [
            'year'     => $year,
            'month'    => $month,
            'expenses' => $expenses,
            'total'    => $totalAmount,
            'currencySymbol' => \App\Support\Currency::symbol(session('currency')),
        ])->setPaper('a4', 'portrait');

        $filename = sprintf('expenses_%d_%02d.pdf', $year, $month);

        return $pdf->download($filename);
    }

    /**
     * Harcama olan ayların listesini (Y-m) döner.
     */
    private function getExpenseMonths(int $userId): array
    {
        try {
            $apiData = $this->api->listExpenses($userId, 0, 200);
            $allExpenses = $apiData['expenses'] ?? [];
        } catch (\Throwable $e) {
            $allExpenses = [];
        }

        $months = [];
        foreach ($allExpenses as $expense) {
            if (empty($expense['expense_date'])) {
                continue;
            }
            $monthKey = Carbon::parse($expense['expense_date'])->format('Y-m');
            $months[$monthKey] = true;
        }

        $keys = array_keys($months);
        sort($keys);

        return $keys;
    }

    /**
     * Belirli yıl/ay için harcamaları döndürür.
     */
    private function fetchMonthExpenses(int $userId, int $year, int $month): array
    {
        try {
            $apiData = $this->api->listExpenses($userId, 0, 200);
            $allExpenses = $apiData['expenses'] ?? [];
        } catch (\Throwable $e) {
            $allExpenses = [];
        }

        $filtered = [];
        foreach ($allExpenses as $expense) {
            if (empty($expense['expense_date'])) {
                continue;
            }
            $date = Carbon::parse($expense['expense_date']);
            if ($date->year !== $year || $date->month !== $month) {
                continue;
            }
            $filtered[] = $expense;
        }

        return $filtered;
    }
}


