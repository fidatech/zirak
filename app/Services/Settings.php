<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Keys the admin can set in Admin → Settings instead of .env.
 * Saved values are encrypted with APP_KEY and override .env when present.
 */
class Settings
{
    /** key => [config path, type, secret?] */
    public const FIELDS = [
        'hesabpay_driver' => ['services.hesabpay.driver', 'string', false],
        'hesabpay_api_key' => ['services.hesabpay.api_key', 'string', true],
        'bunny_library_id' => ['services.bunny.library_id', 'string', false],
        'bunny_api_key' => ['services.bunny.api_key', 'string', true],
        'bunny_read_only_key' => ['services.bunny.read_only_key', 'string', true],
        'tutor_enabled' => ['zirak.tutor.enabled', 'bool', false],
        'tutor_driver' => ['zirak.tutor.driver', 'string', false],
        'tutor_model' => ['zirak.tutor.model', 'string', false],
        'tutor_daily_limit' => ['zirak.tutor.daily_limit', 'int', false],
        'anthropic_api_key' => ['zirak.tutor.api_key', 'string', true],
        'telegram_bot_token' => ['services.telegram.bot_token', 'string', true],
        'telegram_bot_username' => ['services.telegram.bot_username', 'string', false],
        'telegram_webhook_secret' => ['services.telegram.webhook_secret', 'string', true],
    ];

    private const CACHE_KEY = 'zirak:settings:encrypted';

    /** Put saved values over the .env config. Called once per request at boot. */
    public function apply(): void
    {
        foreach ($this->saved() as $key => $value) {
            [$path, $type] = self::FIELDS[$key];
            config([$path => $this->cast($value, $type)]);
        }

        if (isset($this->saved()['hesabpay_driver']) && ! env('HESABPAY_BASE_URL')) {
            config(['services.hesabpay.base_url' => config('services.hesabpay.driver') === 'live' ? 'https://api.hesab.com' : 'https://api-sandbox.hesab.com']);
        }
    }

    /** Decrypted saved values (only keys that were saved in admin). */
    public function saved(): array
    {
        try {
            $rows = Cache::get(self::CACHE_KEY);
            if ($rows === null) {
                $rows = Setting::whereIn('key', array_keys(self::FIELDS))->pluck('value', 'key')->all();
                Cache::forever(self::CACHE_KEY, $rows); // still encrypted in the cache
            }
        } catch (Throwable) {
            return []; // no database or tables yet (fresh install, composer scripts): use .env
        }

        $values = [];
        foreach ($rows as $key => $encrypted) {
            try {
                $values[$key] = Crypt::decryptString($encrypted);
            } catch (DecryptException) {
                Log::warning("Setting {$key} could not be decrypted (APP_KEY changed?). Enter it again in Admin → Settings.");
            }
        }

        return $values;
    }

    /** Current value (saved, else .env), cast to its type. */
    public function get(string $key): mixed
    {
        return config(self::FIELDS[$key][0]);
    }

    public function isSaved(string $key): bool
    {
        return array_key_exists($key, $this->saved());
    }

    /** Save values. Blank secrets are skipped so an empty password box keeps the saved key. */
    public function save(array $values, ?User $by = null): void
    {
        foreach ($values as $key => $value) {
            if (! isset(self::FIELDS[$key])) {
                continue;
            }
            [, $type, $secret] = self::FIELDS[$key];
            if ($secret && blank($value)) {
                continue;
            }
            if (blank($value) && $type !== 'bool') {
                Setting::where('key', $key)->delete(); // back to the .env value

                continue;
            }
            $plain = $type === 'bool' ? ($value ? '1' : '0') : trim((string) $value);
            Setting::updateOrCreate(['key' => $key], ['value' => Crypt::encryptString($plain), 'updated_by' => $by?->id]);
        }
        $this->refresh();
    }

    public function forget(string $key): void
    {
        Setting::where('key', $key)->delete();
        $this->refresh();
    }

    public function refresh(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->apply();
    }

    /** "…1234" so the admin can recognise a saved key without seeing it. */
    public function hint(string $key): ?string
    {
        $value = (string) $this->get($key);

        return $value === '' ? null : '…'.substr($value, -4);
    }

    private function cast(string $value, string $type): mixed
    {
        return match ($type) {
            'bool' => $value === '1',
            'int' => (int) $value,
            default => $value,
        };
    }
}
