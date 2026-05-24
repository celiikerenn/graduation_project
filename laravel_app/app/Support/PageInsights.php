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
