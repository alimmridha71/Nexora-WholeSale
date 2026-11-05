<?php

namespace App\Support;

use NumberFormatter;

/**
 * Single place for price formatting. Used in Blade through @money(...).
 */
final class Money
{
    public static function format(string|int|float|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        $currency = (string) config('nexora.currency', 'USD');

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter(app()->getLocale(), NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency((float) $amount, $currency);

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return $currency.' '.number_format((float) $amount, 2);
    }
}
