<?php

namespace App\Support;

/**
 * Contextual tip cards shown on key pages (rule-based, no API call).
 *
 * @phpstan-type Insight array{title: string, text: string, icon: string, variant: string}
 */
class PageInsights
{
    /**
     * @return list<Insight>
     */
    public static function forDashboard(?array $monthly, float $monthlyBudget, array $recentMonths): array
    {
        $insights = [];
        $spent = (float) ($monthly['total_amount'] ?? 0);
        $count = (int) ($monthly['expense_count'] ?? 0);

        if ($monthlyBudget <= 0) {
            $insights[] = self::card(
                'Budget tip',
                'Set a monthly budget in Settings to track usage and get alerts.',
                'bulb',
                'amber',
            );
        } elseif ($spent > 0) {
            $pct = ($spent / $monthlyBudget) * 100;
            if ($pct > 100) {
                $insights[] = self::card(
                    'Budget tip',
                    'You have exceeded your monthly budget. Review recent expenses.',
                    'bulb',
                    'amber',
                );
            } elseif ($pct > 80) {
                $insights[] = self::card(
                    'Budget tip',
                    'More than 80% of your budget is used — watch spending for the rest of the month.',
                    'bulb',
                    'amber',
                );
            } elseif ($pct < 30 && $count > 0) {
                $insights[] = self::card(
                    'Budget tip',
                    'You are well within budget so far this month.',
                    'bulb',
                    'amber',
                );
            }
        }

        if ($count === 0 && !empty($monthly)) {
            $insights[] = self::card(
                'Get started',
                'No expenses this month yet. Add one manually or scan a receipt.',
                'info-circle',
                'teal',
            );
        }

        if (count($recentMonths) >= 2) {
            $latest = (float) ($recentMonths[0]['total'] ?? 0);
            $previous = (float) ($recentMonths[1]['total'] ?? 0);
            if ($previous > 0 && $latest > $previous * 1.15) {
                $insights[] = self::card(
                    'Tip',
                    'Your most recent month spent more than the month before it.',
                    'info-circle',
                    'teal',
                );
            }
        }

        return array_slice($insights, 0, 3);
    }

    /**
     * @return list<Insight>
     */
    public static function forReports(int $totalMonths): array
    {
        if ($totalMonths === 0) {
            return [self::card(
                'Note',
                'Add expenses first — monthly CSV and PDF exports will appear here.',
                'pin',
                'gray',
            )];
        }

        $insights = [self::card(
            'Note',
            'Select multiple months, then download a ZIP of CSV or PDF files in one go.',
            'pin',
            'gray',
        )];

        if ($totalMonths >= 3) {
            $insights[] = self::card(
                'Note',
                'Use “Select this page” to batch-export a full page of months quickly.',
                'pin',
                'gray',
            );
        }

        return array_slice($insights, 0, 2);
    }

    /**
     * @return list<Insight>
     */
    public static function forExpenses(int $total, bool $filtersActive, int $monthCount): array
    {
        $insights = [];

        if ($total === 0) {
            $insights[] = self::card(
                'Quick tip',
                'Start with Receipt Scan for fast entry from a photo, or add an expense manually.',
                'info-circle',
                'blue',
            );

            return $insights;
        }

        if ($filtersActive) {
            $insights[] = self::card(
                'Quick tip',
                'Filters are on — clear date or category filters to jump back to a full month view.',
                'info-circle',
                'blue',
            );
        } elseif ($monthCount > 1) {
            $insights[] = self::card(
                'Quick tip',
                'Use the month picker in the sidebar to browse expenses by month.',
                'info-circle',
                'blue',
            );
        }

        $insights[] = self::card(
            'Quick tip',
            'Receipt Scan fills amount, date and category from a photo — you confirm before saving.',
            'info-circle',
            'blue',
        );

        return array_slice($insights, 0, 2);
    }

    /**
     * @param  array<string, mixed>|null  $scan
     * @return list<Insight>
     */
    public static function forReceiptScan(?array $scan): array
    {
        if (empty($scan)) {
            return [
                self::card(
                    'Photo tip',
                    'Use a clear, well-lit photo. Turkish receipts work best with the total (TOPLAM) visible.',
                    'bulb',
                    'amber',
                ),
                self::card(
                    'Before saving',
                    'After scanning, always review amount and category before saving.',
                    'circle-check',
                    'teal',
                ),
            ];
        }

        $insights = [];
        $confidence = $scan['confidence'] ?? 'low';
        $source = $scan['description_source'] ?? null;

        if ($confidence === 'low') {
            $insights[] = self::card(
                'Before saving',
                'Low confidence read — double-check amount, date and category.',
                'circle-check',
                'teal',
            );
        } elseif ($confidence === 'medium') {
            $insights[] = self::card(
                'Before saving',
                'Medium confidence — a quick review of the fields is recommended.',
                'circle-check',
                'teal',
            );
        }

        if ($source === 'ai') {
            $insights[] = self::card(
                'Before saving',
                'Merchant name was suggested by AI from the receipt text.',
                'circle-check',
                'teal',
            );
        } elseif ($source === 'ocr') {
            $insights[] = self::card(
                'Before saving',
                'Description comes from OCR text on the receipt.',
                'circle-check',
                'teal',
            );
        }

        if (empty($insights)) {
            $insights[] = self::card(
                'Before saving',
                'Fields look good — adjust anything needed, then save the expense.',
                'circle-check',
                'teal',
            );
        }

        return array_slice($insights, 0, 2);
    }

    /**
     * @return list<Insight>
     */
    public static function forCreateExpense(): array
    {
        return [self::card(
            'Reminder',
            'Mark recurring bills as fixed expenses to add them quickly each month.',
            'pin',
            'purple',
        )];
    }

    /**
     * @return Insight
     */
    private static function card(string $title, string $text, string $icon, string $variant): array
    {
        return [
            'title' => $title,
            'text' => $text,
            'icon' => $icon,
            'variant' => $variant,
        ];
    }
}
