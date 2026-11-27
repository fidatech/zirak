<?php

namespace App\Support;

class Phone
{
    /**
     * Normalise a phone number to E.164. Accepts Persian/Arabic digits and Afghan local format (07XXXXXXXX).
     */
    public static function normalize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $value = Digits::toLatin($input);
        $value = preg_replace('/[^\d+]/', '', $value);

        if (str_starts_with($value, '00')) {
            $value = '+'.substr($value, 2);
        }
        if (preg_match('/^0?(7\d{8})$/', $value, $m)) { // 0790000000, or 790000000 typed after the +93 box
            $value = '+93'.$m[1];
        }
        if (preg_match('/^93(7\d{8})$/', $value, $m)) {
            $value = '+93'.$m[1];
        }

        return preg_match('/^\+[1-9]\d{7,14}$/', $value) ? $value : null;
    }
}
