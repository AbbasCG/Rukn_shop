<?php

namespace App\Helpers;

class LocaleNumber
{
    /**
     * Convert digits based on locale.
     *
     * - Arabic locale: 0-9 -> ٠-٩
     * - Other locales: unchanged string value
     */
    public static function digits($value, ?string $locale = null): string
    {
        $stringValue = (string) $value;
        $activeLocale = $locale ?? app()->getLocale();

        if ($activeLocale !== 'ar') {
            return $stringValue;
        }

        return strtr($stringValue, [
            '0' => '٠',
            '1' => '١',
            '2' => '٢',
            '3' => '٣',
            '4' => '٤',
            '5' => '٥',
            '6' => '٦',
            '7' => '٧',
            '8' => '٨',
            '9' => '٩',
        ]);
    }
}
