<?php

namespace App\Filament\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

/** A small built-in person picture instead of Filament's default ui-avatars.com image (no outside request, works offline). */
class LocalAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40"><rect width="40" height="40" rx="20" fill="#ccfbf1"/>'
            .'<circle cx="20" cy="16" r="7" fill="#0f766e"/><path d="M7 34c2-7 7-10 13-10s11 3 13 10" fill="#0f766e"/></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
