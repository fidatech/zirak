<?php

namespace App\Services\Telegram;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Minimal Telegram Bot API client (https://core.telegram.org/bots/api). */
class TelegramClient
{
    public function configured(): bool
    {
        return filled(config('services.telegram.bot_token')) && filled(config('services.telegram.bot_username'));
    }

    public function call(string $method, array $params = []): ?Response
    {
        if (! filled(config('services.telegram.bot_token'))) {
            return null;
        }

        $response = Http::asJson()->timeout(15)
            ->post('https://api.telegram.org/bot'.config('services.telegram.bot_token').'/'.$method, $params);

        if (! $response->successful()) {
            Log::warning('Telegram API '.$method.' failed', ['status' => $response->status(), 'description' => $response->json('description')]);
        }

        return $response;
    }

    /** @param  array<int, array<int, array{text:string, url?:string, callback_data?:string}>>  $buttons */
    public function send(int|string $chatId, string $text, array $buttons = []): ?Response
    {
        return $this->call('sendMessage', array_filter([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => $buttons ? ['inline_keyboard' => $buttons] : null,
            'link_preview_options' => ['is_disabled' => true],
        ]));
    }

    public function edit(int|string $chatId, int $messageId, string $text, array $buttons = []): ?Response
    {
        return $this->call('editMessageText', array_filter([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'reply_markup' => $buttons ? ['inline_keyboard' => $buttons] : null,
        ]));
    }

    public function answerCallback(string $callbackId, ?string $text = null): ?Response
    {
        return $this->call('answerCallbackQuery', array_filter(['callback_query_id' => $callbackId, 'text' => $text]));
    }

    public function link(string $payload): string
    {
        return 'https://t.me/'.config('services.telegram.bot_username').'?start='.$payload;
    }

    /** Telegram language code -> Zirak locale. */
    public static function locale(?string $code): string
    {
        return match (substr((string) $code, 0, 2)) {
            'ps' => 'ps',
            'en' => 'en',
            default => 'prs',
        };
    }
}
