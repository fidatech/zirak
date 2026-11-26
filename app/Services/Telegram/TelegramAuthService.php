<?php

namespace App\Services\Telegram;

use App\Models\TelegramLogin;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Log in / link accounts with Telegram using a bot deep link:
 * the browser gets a one-time token, the student opens t.me/<bot>?start=login_<token>,
 * confirms in Telegram, and the waiting browser (same session only) is logged in.
 */
class TelegramAuthService
{
    public const TTL_MINUTES = 10;

    public function __construct(private TelegramClient $telegram) {}

    /** @return array{0: TelegramLogin, 1: string} the record and the plain token (kept in the session) */
    public function start(string $purpose, ?User $user, ?string $userAgent): array
    {
        $token = Str::random(32);

        $login = TelegramLogin::create([
            'token_hash' => hash('sha256', $token),
            'purpose' => $purpose,
            'user_id' => $user?->id,
            'device' => self::describeDevice($userAgent),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        return [$login, $token];
    }

    public function deepLink(string $token): string
    {
        return $this->telegram->link('login_'.$token);
    }

    /** The student pressed Start in Telegram: remember who they are and ask them to confirm. */
    public function received(string $token, array $from, int|string $chatId): void
    {
        $login = TelegramLogin::findByToken($token);

        if (! $login || $login->status !== 'pending' || $login->isExpired()) {
            $this->telegram->send($chatId, __('app.telegram.expired'));

            return;
        }

        $login->update([
            'telegram_id' => $from['id'],
            'telegram_username' => $from['username'] ?? null,
            'telegram_name' => trim(($from['first_name'] ?? '').' '.($from['last_name'] ?? '')) ?: null,
            'telegram_language' => $from['language_code'] ?? null,
        ]);

        $question = $login->purpose === 'link'
            ? __('app.telegram.confirm_link', ['device' => $login->device])
            : __('app.telegram.confirm_login', ['device' => $login->device]);

        $this->telegram->send($chatId, $question."\n\n".__('app.telegram.confirm_warning'), [[
            ['text' => __('app.telegram.confirm_yes'), 'callback_data' => 'tg_ok:'.$login->id],
            ['text' => __('app.telegram.confirm_no'), 'callback_data' => 'tg_no:'.$login->id],
        ]]);
    }

    /** Confirm or cancel from the inline button. Only the Telegram user who pressed Start may answer. */
    public function answer(int $loginId, bool $approve, int|string $fromId): ?TelegramLogin
    {
        $login = TelegramLogin::find($loginId);

        if (! $login || (string) $login->telegram_id !== (string) $fromId || $login->status !== 'pending' || $login->isExpired()) {
            return null;
        }

        $login->update(['status' => $approve ? 'confirmed' : 'cancelled']);

        return $login;
    }

    /** Called by the waiting browser. Returns the user once Telegram confirmed. */
    public function complete(?string $token, ?User $current = null): array
    {
        $login = TelegramLogin::findByToken($token);

        if (! $login || $login->isExpired() && $login->status === 'pending') {
            return ['status' => 'expired'];
        }
        if ($login->status !== 'confirmed') {
            return ['status' => $login->status];
        }

        return DB::transaction(function () use ($login, $current) {
            $login = TelegramLogin::whereKey($login->id)->lockForUpdate()->first();
            if ($login->status !== 'confirmed') {
                return ['status' => $login->status];
            }

            if ($login->purpose === 'link') {
                $user = $login->user;
                abort_unless($user && $current && $user->is($current), 403);
                if (User::where('telegram_id', $login->telegram_id)->whereKeyNot($user->id)->exists()) {
                    $login->update(['status' => 'cancelled']);
                    throw ValidationException::withMessages(['telegram' => __('app.telegram.already_linked')]);
                }
            } else {
                $user = User::firstOrNew(['telegram_id' => $login->telegram_id]);
                if (! $user->exists) {
                    $user->nickname = $login->telegram_name ? mb_substr($login->telegram_name, 0, 60) : null;
                    $user->locale = app()->getLocale();
                }
            }

            $user->forceFill([
                'telegram_id' => $login->telegram_id,
                'telegram_username' => $login->telegram_username,
                'telegram_linked_at' => $user->telegram_linked_at ?? now(),
            ])->save();

            $login->update(['status' => 'used', 'user_id' => $user->id]);

            return ['status' => 'done', 'user' => $user];
        });
    }

    public static function describeDevice(?string $agent): string
    {
        $agent = (string) $agent;
        $browser = collect(['Edg' => 'Edge', 'OPR' => 'Opera', 'SamsungBrowser' => 'Samsung Internet', 'Firefox' => 'Firefox', 'Chrome' => 'Chrome', 'Safari' => 'Safari'])
            ->first(fn ($name, $needle) => str_contains($agent, $needle)) ?? 'Browser';
        $os = collect(['Android' => 'Android', 'iPhone' => 'iPhone', 'iPad' => 'iPad', 'Windows' => 'Windows', 'Mac OS' => 'Mac', 'Linux' => 'Linux'])
            ->first(fn ($name, $needle) => str_contains($agent, $needle)) ?? '';

        return trim($browser.($os ? ' · '.$os : ''));
    }
}
