<?php

namespace App\Http\Controllers;

use App\Services\FastApiService;
use App\Support\Currency;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private function defaultFixedTemplates(): array
    {
        return [];
    }

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
            // API down ise profile yine açılsın
        }

        return view('profile', [
            'name'           => $request->session()->get('user_name'),
            'email'          => $request->session()->get('user_email'),
            'categories'     => $categories,
            'emailNotifications' => (bool) $request->session()->get('email_notifications', true),
            'fixedTemplates' => $request->session()->get('fixed_expense_templates', $this->defaultFixedTemplates()),
            'currency'       => Currency::normalize(session('currency')),
            'monthlyBudget'  => (float) $request->session()->get('monthly_budget', 0),
        ]);
    }

    public function updateBudget(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'monthly_budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        $monthlyBudget = (float) ($validated['monthly_budget'] ?? 0);

        // Bütçeyi FastAPI tarafında güncelle (kullanıcıya bağlı alan)
        $this->api->updateUserBudget($userId, $monthlyBudget);

        // Dashboard hesaplamaları için session'da da tut
        $request->session()->put('monthly_budget', $monthlyBudget);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Monthly budget saved.');
    }

    public function showBudget(Request $request): View|RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $monthlyBudget = (float) ($request->session()->get('monthly_budget', 0));

        return view('profile.budget', [
            'monthlyBudget' => $monthlyBudget,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        try {
            $this->api->changePassword([
                'user_id'          => $userId,
                'current_password' => $request->input('current_password'),
                'new_password'     => $request->input('new_password'),
            ]);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $body = $e->response->json();
            $detail = $body['detail'] ?? null;
            $message = is_string($detail) ? $detail : 'Password change failed.';
            return back()->withErrors(['current_password' => $message]);
        } catch (\Throwable $e) {
            return back()->withErrors(['current_password' => 'Password change failed.']);
        }

        return back()->with('success', 'Password changed successfully.');
    }

    public function updateMonthlyFixedExpenses(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $templates = $request->input('templates', []);
        if (!is_array($templates)) {
            return back()->withErrors(['amount' => 'Invalid fixed expense template payload.'])->withInput();
        }

        $cleaned = [];
        foreach ($templates as $row) {
            $category = trim((string) ($row['category'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));
            $amount = (float) ($row['amount'] ?? 0);

            if ($category === '' || $description === '' || $amount <= 0) {
                continue;
            }

            $cleaned[] = [
                'category' => $category,
                'description' => mb_substr($description, 0, 120),
                'amount' => round($amount, 2),
            ];
        }

        $request->session()->put('fixed_expense_templates', $cleaned);
        return redirect()->route('profile.show')->with('success', 'Monthly fixed expense templates updated.');
    }

    public function updateCurrency(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'currency' => ['required', 'string', 'in:TRY,USD,EUR,GBP'],
        ]);

        $code = Currency::normalize($validated['currency']);
        $email = (string) $request->session()->get('user_email', '');
        if ($email !== '') {
            User::where('email', $email)->update(['currency' => $code]);
        }
        $request->session()->put('currency', $code);

        return redirect()->route('profile.show')->with('success', 'Currency preference saved.');
    }

    public function updateEmailNotifications(Request $request): RedirectResponse|JsonResponse
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $enabled = $request->input('email_notifications') === '1'
            || $request->input('email_notifications') === 1;

        try {
            $this->api->updateNotificationSettings((int) $userId, $enabled);
        } catch (\Throwable $e) {
            $message = 'Could not update notification settings.';
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $message], 422);
            }

            return back()->withErrors(['email_notifications' => $message]);
        }

        $request->session()->put('email_notifications', $enabled);

        $message = $enabled
            ? 'Email alerts enabled.'
            : 'Email alerts disabled.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'enabled' => $enabled,
                'message' => $message,
            ]);
        }

        return redirect()->route('profile.show')->with('success', $message);
    }
}

