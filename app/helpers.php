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

if (! function_exists('data_saver')) {
    /** Save mobile data: the student's own setting, or the browser's "Save-Data: on" header (Android data saver). */
    function data_saver(): bool
    {
        return (bool) auth()->user()?->data_saver || strtolower((string) request()->header('Save-Data')) === 'on';
    }
}

if (! function_exists('sep')) {
    /** List separator. Dari/Pashto use a dash: a middle dot next to Persian digits reads as a zero (۰). */
    function sep(): string
    {
        return text_dir() === 'rtl' ? ' – ' : ' · ';
    }
}

if (! function_exists('duration')) {
    /** "2 h 15 min" / "۲ ساعت و ۱۵ دقیقه" from minutes. */
    function duration(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        if ($h === 0) {
            return __('app.time.m', ['n' => n(max(1, $m))]);
        }

        return $m === 0 ? __('app.time.h', ['n' => n($h)]) : __('app.time.hm', ['h' => n($h), 'm' => n($m)]);
    }
}
