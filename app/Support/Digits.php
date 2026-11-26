<?php

namespace App\Support;

class Digits
{
    private const PERSIAN = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    private const ARABIC = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    public static function toLatin(string $value): string
    {
        return str_replace(self::ARABIC, range(0, 9), str_replace(self::PERSIAN, range(0, 9), $value));
    }

    /** Show digits in Persian numerals for Pashto/Dari, Latin for English. */
    public static function localize(int|float|string|null $value): string
    {
        $value = (string) $value;

        return in_array(app()->getLocale(), ['prs', 'ps'], true)
            ? str_replace(range(0, 9), self::PERSIAN, $value)
            : $value;
    }
}
