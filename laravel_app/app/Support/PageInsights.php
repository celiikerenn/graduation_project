<?php

namespace App\Support;

/**
 * Short contextual tips shown in AI insight chips (rule-based, no API call).
 */
class PageInsights
{
    /**
     * @return list<array{text: string, tone: string}>
     */
    public static function forDashboard(?array $monthly, float $monthlyBudget, array $recentMonths): array
    {
        $insights = [];
        $spent = (float) ($monthly['total_amount'] ?? 0);
        $count = (int) ($monthly['expense_count'] ?? 0);

        if ($monthlyBudget <= 0) {
            $insights[] = [
                'text' => 'Set a monthly budget in Settings to track usage and get alerts.',
                'tone' => 'tip',
            ];
        } elseif ($spent > 0) {
            $pct = ($spent / $monthlyBudget) * 100;
            if ($pct > 100) {
                $insights[] = [
                    'text' => 'You have exceeded your monthly budget. Review recent expenses.',
                    'tone' => 'warn',
                ];
            } elseif ($pct > 80) {
                $insights[] = [
                    'text' => 'More than 80% of your budget is used — watch spending for the rest of the month.',
                    'tone' => 'warn',
                ];
            } elseif ($pct < 30 && $count > 0) {
                $insights[] = [
                    'text' => 'You are well within budget so far this month.',
                    'tone' => 'info',
                ];
            }
        }

        if ($count === 0 && !empty($monthly)) {
            $insights[] = [
                'text' => 'No expenses this month yet. Add one manually or scan a receipt.',
                'tone' => 'tip',
            ];
        }

        if (count($recentMonths) >= 2) {
            $latest = (float) ($recentMonths[0]['total'] ?? 0);
            $previous = (float) ($recentMonths[1]['total'] ?? 0);
            if ($previous > 0 && $latest > $previous * 1.15) {
                $insights[] = [
                    'text' => 'Your most recent month spent more than the month before it.',
                    'tone' => 'info',
                ];
            }
        }

        return array_slice($insights, 0, 3);
    }

    /**
     * @return list<array{text: string, tone: string}>
     */
    public static function forReports(int $totalMonths): array
    {
        if ($totalMonths === 0) {
            return [[
                'text' => 'Add expenses first — monthly CSV and PDF exports will appear here.',
                'tone' => 'tip',
            ]];
        }

        $insights = [[
            'text' => 'Select multiple months, then download a ZIP of CSV or PDF files in one go.',
            'tone' => 'tip',
        ]];

        if ($totalMonths >= 3) {
            $insights[] = [
                'text' => 'Use “Select this page” to batch-export a full page of months quickly.',
                'tone' => 'info',
            ];
        }

        return array_slice($insights, 0, 2);
    }

    /**
     * @return list<array{text: string, tone: string}>
     */
    public static function forExpenses(int $total, bool $filtersActive, int $monthCount): array
    {
        $insights = [];

        if ($total === 0) {
            $insights[] = [
                'text' => 'Start with Receipt Scan for fast entry from a photo, or add an expense manually.',
                'tone' => 'tip',
            ];

            return $insights;
        }

        if ($filtersActive) {
            $insights[] = [
                'text' => 'Filters are on — clear date or category filters to jump back to a full month view.',
                'tone' => 'info',
            ];
        } elseif ($monthCount > 1) {
            $insights[] = [
                'text' => 'Use the month picker in the sidebar to browse expenses by month.',
                'tone' => 'tip',
            ];
        }

        $insights[] = [
            'text' => 'Receipt Scan fills amount, date and category from a photo — you confirm before saving.',
            'tone' => 'info',
        ];

        return array_slice($insights, 0, 2);
    }

    /**
     * @param  array<string, mixed>|null  $scan
     * @return list<array{text: string, tone: string}>
     */
    public static function forReceiptScan(?array $scan): array
    {
        if (empty($scan)) {
            return [
                [
                    'text' => 'Use a clear, well-lit photo. Turkish receipts work best with the total (TOPLAM) visible.',
                    'tone' => 'tip',
                ],
                [
                    'text' => 'After scanning, always review amount and category before saving.',
                    'tone' => 'info',
                ],
            ];
        }

        $insights = [];
        $confidence = $scan['confidence'] ?? 'low';
        $source = $scan['description_source'] ?? null;

        if ($confidence === 'low') {
            $insights[] = [
                'text' => 'Low confidence read — double-check amount, date and category.',
                'tone' => 'warn',
            ];
        } elseif ($confidence === 'medium') {
            $insights[] = [
                'text' => 'Medium confidence — a quick review of the fields is recommended.',
                'tone' => 'info',
            ];
        }

        if ($source === 'ai') {
            $insights[] = [
                'text' => 'Merchant name was suggested by AI from the receipt text.',
                'tone' => 'info',
            ];
        } elseif ($source === 'ocr') {
            $insights[] = [
                'text' => 'Description comes from OCR text on the receipt.',
                'tone' => 'info',
            ];
        }

        if (empty($insights)) {
            $insights[] = [
                'text' => 'Fields look good — adjust anything needed, then save the expense.',
                'tone' => 'tip',
            ];
        }

        return array_slice($insights, 0, 2);
    }

    /**
     * @return list<array{text: string, tone: string}>
     */
    public static function forCreateExpense(): array
    {
        return [[
            'text' => 'Mark recurring bills as fixed expenses to add them quickly each month.',
            'tone' => 'tip',
        ]];
    }
}
