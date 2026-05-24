<?php

namespace App\Http\Controllers;

use App\Services\FastApiService;
use App\Support\Currency;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

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

        $summaries = $this->buildMonthSummaries($userId);
        $months = array_column($summaries, 'key');

        $perPage = 12;
        $page = max((int) $request->query('page', 1), 1);
        $totalMonths = count($months);
        $totalPages = max((int) ceil($totalMonths / $perPage), 1);
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $pageSummaries = array_slice($summaries, $offset, $perPage);

        return view('reports.index', [
            'monthCards'     => $pageSummaries,
            'page'           => $page,
            'totalPages'     => $totalPages,
            'totalMonths'    => $totalMonths,
            'currencySymbol' => Currency::symbol(session('currency')),
        ]);
    }

    public function downloadCsv(Request $request, int $year, int $month): Response|RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        if (!$this->monthHasExpenses($userId, $year, $month)) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'No expenses found for the selected month.']);
        }

        $expenses = $this->fetchMonthExpenses($userId, $year, $month);
        $csvContent = $this->buildCsvContent($expenses);
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

        if (!$this->monthHasExpenses($userId, $year, $month)) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'No expenses found for the selected month.']);
        }

        $expenses = $this->fetchMonthExpenses($userId, $year, $month);
        $filename = sprintf('expenses_%d_%02d.pdf', $year, $month);

        return $this->buildPdfResponse($expenses, $year, $month, $filename);
    }

    public function downloadAllCsvZip(Request $request): Response|RedirectResponse
    {
        return $this->downloadMonthsZip($request, 'csv', null, 'expenses_all_csv.zip');
    }

    public function downloadAllPdfZip(Request $request): Response|RedirectResponse
    {
        return $this->downloadMonthsZip($request, 'pdf', null, 'expenses_all_pdf.zip');
    }

    public function downloadSelectedCsvZip(Request $request): Response|RedirectResponse
    {
        return $this->downloadMonthsZip(
            $request,
            'csv',
            $request->input('months', []),
            'expenses_selected_csv.zip'
        );
    }

    public function downloadSelectedPdfZip(Request $request): Response|RedirectResponse
    {
        return $this->downloadMonthsZip(
            $request,
            'pdf',
            $request->input('months', []),
            'expenses_selected_pdf.zip'
        );
    }

    /**
     * @param  list<string>|null  $requestedMonthKeys  null = all months with expenses
     */
    private function downloadMonthsZip(
        Request $request,
        string $format,
        ?array $requestedMonthKeys,
        string $zipFilename
    ): Response|RedirectResponse {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        if ($format === 'pdf' && !class_exists(ZipArchive::class)) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'ZIP extension is not available on the server.']);
        }

        $summaries = $this->buildMonthSummaries($userId);
        if ($summaries === []) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'No expenses found yet.']);
        }

        if ($requestedMonthKeys === null) {
            $monthKeys = array_column($summaries, 'key');
        } else {
            $monthKeys = $this->filterValidMonthKeys($userId, $requestedMonthKeys);
            if ($monthKeys === []) {
                return redirect()->route('reports.index')
                    ->withErrors(['reports' => 'Select at least one month to download.']);
            }
        }

        $summaryByKey = [];
        foreach ($summaries as $summary) {
            $summaryByKey[$summary['key']] = $summary;
        }

        $allExpenses = $this->listAllExpenses($userId);
        $currencySymbol = Currency::symbol(session('currency'));
        $files = [];

        foreach ($monthKeys as $key) {
            if (!isset($summaryByKey[$key])) {
                continue;
            }
            [$year, $month] = $this->parseMonthKey($key);
            $expenses = $this->filterExpensesByMonth($allExpenses, $year, $month);
            if ($format === 'csv') {
                $files[sprintf('expenses_%d_%02d.csv', $year, $month)] = $this->buildCsvContent($expenses);
            } else {
                $files[sprintf('expenses_%d_%02d.pdf', $year, $month)] = $this->buildPdfBinary(
                    $expenses,
                    $year,
                    $month,
                    $currencySymbol
                );
            }
        }

        if ($files === []) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'No valid months to download.']);
        }

        return $this->zipDownloadResponse($files, $zipFilename);
    }

    /**
     * @param  list<mixed>  $requestedKeys
     * @return list<string>
     */
    private function filterValidMonthKeys(int $userId, array $requestedKeys): array
    {
        $allowed = array_flip(array_column($this->buildMonthSummaries($userId), 'key'));
        $valid = [];

        foreach ($requestedKeys as $key) {
            if (!is_string($key) || !preg_match('/^\d{4}-\d{2}$/', $key) || !isset($allowed[$key])) {
                continue;
            }
            $valid[$key] = true;
        }

        $keys = array_keys($valid);
        sort($keys);

        return $keys;
    }

    /**
     * @return list<array{key: string, label: string, total: float, count: int}>
     */
    private function buildMonthSummaries(int $userId): array
    {
        $allExpenses = $this->listAllExpenses($userId);
        $buckets = [];

        foreach ($allExpenses as $expense) {
            if (empty($expense['expense_date'])) {
                continue;
            }
            $date = Carbon::parse($expense['expense_date']);
            $key = $date->format('Y-m');
            if (!isset($buckets[$key])) {
                $buckets[$key] = ['total' => 0.0, 'count' => 0];
            }
            $buckets[$key]['total'] += (float) ($expense['amount'] ?? 0);
            $buckets[$key]['count']++;
        }

        $keys = array_keys($buckets);
        sort($keys);

        $summaries = [];
        foreach ($keys as $key) {
            try {
                $label = Carbon::createFromFormat('Y-m', $key)->format('F Y');
            } catch (\Throwable) {
                $label = $key;
            }

            $summaries[] = [
                'key'   => $key,
                'label' => $label,
                'total' => $buckets[$key]['total'],
                'count' => $buckets[$key]['count'],
            ];
        }

        return $summaries;
    }

    private function monthHasExpenses(int $userId, int $year, int $month): bool
    {
        $key = sprintf('%04d-%02d', $year, $month);

        foreach ($this->buildMonthSummaries($userId) as $summary) {
            if ($summary['key'] === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function listAllExpenses(int $userId): array
    {
        try {
            $apiData = $this->api->listExpenses($userId, 0, 200);

            return $apiData['expenses'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchMonthExpenses(int $userId, int $year, int $month): array
    {
        return $this->filterExpensesByMonth($this->listAllExpenses($userId), $year, $month);
    }

    /**
     * @param  list<array<string, mixed>>  $allExpenses
     * @return list<array<string, mixed>>
     */
    private function filterExpensesByMonth(array $allExpenses, int $year, int $month): array
    {
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

    /**
     * @param  list<array<string, mixed>>  $expenses
     */
    private function buildCsvContent(array $expenses): string
    {
        $totalAmount = 0.0;
        foreach ($expenses as $expense) {
            $totalAmount += (float) ($expense['amount'] ?? 0);
        }

        $rows = [['Date', 'Category', 'Description', 'Amount']];
        foreach ($expenses as $expense) {
            $date = Carbon::parse($expense['expense_date']);
            $rows[] = [
                $date->format('Y-m-d'),
                (string) ($expense['category_name'] ?? ''),
                (string) ($expense['description'] ?? ''),
                (string) ($expense['amount'] ?? '0'),
            ];
        }
        $rows[] = ['', '', 'Total', (string) $totalAmount];

        $csvLines = [];
        foreach ($rows as $row) {
            $escaped = array_map(function (string $field): string {
                $field = str_replace('"', '""', $field);

                return '"'.$field.'"';
            }, $row);
            $csvLines[] = implode(',', $escaped);
        }

        return implode("\n", $csvLines);
    }

    /**
     * @param  list<array<string, mixed>>  $expenses
     */
    private function buildPdfBinary(array $expenses, int $year, int $month, string $currencySymbol): string
    {
        $totalAmount = 0.0;
        foreach ($expenses as $expense) {
            $totalAmount += (float) ($expense['amount'] ?? 0);
        }

        $pieChartBase64 = null;
        $barChartBase64 = null;
        $userId = session('user_id');
        // DomPDF needs the PHP GD extension to embed PNG chart images.
        if ($userId && extension_loaded('gd')) {
            try {
                $charts = $this->api->getReportChartImages((int) $userId, $year, $month);
                $pieChartBase64 = $charts['pie_png_base64'] ?? null;
                $barChartBase64 = $charts['bar_png_base64'] ?? null;
            } catch (\Throwable $e) {
                // PDF still generated without charts
            }
        }

        return Pdf::loadView('reports.pdf', [
            'year'           => $year,
            'month'          => $month,
            'expenses'       => $expenses,
            'total'          => $totalAmount,
            'currencySymbol' => $currencySymbol,
            'pieChartBase64' => $pieChartBase64,
            'barChartBase64' => $barChartBase64,
        ])->setPaper('a4', 'portrait')->output();
    }

    /**
     * @param  list<array<string, mixed>>  $expenses
     */
    private function buildPdfResponse(array $expenses, int $year, int $month, string $filename): Response
    {
        $pdf = $this->buildPdfBinary(
            $expenses,
            $year,
            $month,
            Currency::symbol(session('currency'))
        );

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @param  array<string, string>  $files
     */
    private function zipDownloadResponse(array $files, string $zipFilename): Response|RedirectResponse
    {
        if (!class_exists(ZipArchive::class)) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'ZIP extension is not available on the server.']);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'fintrack_reports_');
        if ($tmp === false) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'Could not create archive.']);
        }

        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);

            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'Could not create archive.']);
        }

        foreach ($files as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        $binary = file_get_contents($tmp);
        @unlink($tmp);

        if ($binary === false) {
            return redirect()->route('reports.index')
                ->withErrors(['reports' => 'Could not read archive.']);
        }

        return response($binary, 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$zipFilename.'"',
        ]);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function parseMonthKey(string $key): array
    {
        [$year, $month] = explode('-', $key);

        return [(int) $year, (int) $month];
    }
}
