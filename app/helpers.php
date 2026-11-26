<?php

use App\Support\Digits;

if (! function_exists('n')) {
    /** Localised digits (Persian numerals in Dari/Pashto). */
    function n(int|float|string|null $value): string
    {
        return Digits::localize($value);
    }
}

if (! function_exists('afn')) {
    function afn(int $amount): string
    {
        return n(number_format($amount)).' '.__('app.afn');
    }
}

if (! function_exists('text_dir')) {
    function text_dir(): string
    {
        return config('zirak.locales.'.app()->getLocale().'.dir', 'rtl');
    }
}

if (! function_exists('pct')) {
    function pct(int|float|null $value): string
    {
        return n((int) $value).(in_array(app()->getLocale(), ['prs', 'ps'], true) ? '٪' : '%');
    }
}
