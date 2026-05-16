<?php

namespace App\Http\Controllers;

use App\Services\FastApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptScanController extends Controller
{
    public function __construct(
        protected FastApiService $api
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $categories = [];
        try {
            $categories = $this->api->listCategories();
        } catch (\Throwable $e) {
            // API down
        }

        return view('expenses.receipt-scan', [
            'categories' => $categories,
            'scan' => $request->session()->get('receipt_scan'),
            'receiptPreview' => $request->session()->get('receipt_scan_preview'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'receipt' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        try {
            $categories = $this->api->listCategories();
            $uploaded = $request->file('receipt');
            $result = $this->api->scanReceipt((int) $userId, $uploaded);
            $result['category_id'] = $this->resolveCategoryId($categories, $result['category_name'] ?? null);
            $request->session()->put('receipt_scan', $result);
            $request->session()->put('receipt_scan_preview', [
                'mime' => $uploaded->getMimeType() ?: 'image/jpeg',
                'data' => base64_encode((string) file_get_contents($uploaded->getRealPath())),
            ]);

            return redirect()
                ->route('expenses.receipt-scan')
                ->with('success', $result['message'] ?? 'Receipt scanned. Please review the fields below.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('expenses.receipt-scan')
                ->withErrors(['receipt' => $e->getMessage()]);
        }
    }

    public function confirm(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        if (!$request->session()->has('receipt_scan')) {
            return redirect()
                ->route('expenses.receipt-scan')
                ->withErrors(['receipt' => 'Scan a receipt first.']);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $this->api->createExpense((int) $userId, $validated);
            $request->session()->forget(['receipt_scan', 'receipt_scan_preview']);

            return redirect()
                ->route('expenses.index')
                ->with('success', 'Expense saved from receipt scan.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('expenses.receipt-scan')
                ->withErrors(['amount' => $e->getMessage()]);
        }
    }

    public function discard(Request $request): RedirectResponse
    {
        $request->session()->forget(['receipt_scan', 'receipt_scan_preview']);

        return redirect()
            ->route('expenses.receipt-scan')
            ->with('success', 'Scan discarded. You can upload another receipt.');
    }

    /**
     * @param  list<array{id: int, name: string}>  $categories
     */
    private function resolveCategoryId(array $categories, ?string $categoryName): ?int
    {
        if ($categoryName === null || $categoryName === '') {
            return null;
        }

        foreach ($categories as $cat) {
            if (strcasecmp((string) ($cat['name'] ?? ''), $categoryName) === 0) {
                return (int) $cat['id'];
            }
        }

        return null;
    }
}
