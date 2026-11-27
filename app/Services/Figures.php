<?php

namespace App\Services;

use Illuminate\Support\Number;

/**
 * Formats the trip figures for display. Each returns null for a missing value, so views can show a dash.
 */
class Figures
{
    /**
     * "€1,234", or "€1,234.50" when there are cents.
     */
    public static function euros(?float $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        $hasCents = round($amount * 100) % 100 !== 0;

        return Number::currency($amount, in: 'EUR', locale: 'en', precision: $hasCents ? 2 : 0);
    }

    /**
     * "182,000" or "131.5".
     */
    public static function number(int|float|null $value, int $maxDecimals = 0): ?string
    {
        if ($value === null) {
            return null;
        }

        return Number::format($value, maxPrecision: $maxDecimals, locale: 'en');
    }
}
