<?php

namespace App\Support;

class Currency
{
    public const ALLOWED = ['TRY', 'USD', 'EUR', 'GBP'];

    public static function symbol(?string $code): string
    {
        return match (strtoupper((string) ($code ?: 'TRY'))) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => '₺',
        };
    }

    public static function normalize(?string $code): string
    {
        $u = strtoupper((string) $code);
        return in_array($u, self::ALLOWED, true) ? $u : 'TRY';
    }
}
