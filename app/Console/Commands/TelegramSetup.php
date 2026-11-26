<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('zirak:telegram-setup')]
#[Description('Register the Telegram webhook and bot commands (run once after setting TELEGRAM_* in .env)')]
class TelegramSetup extends Command
{
    public function handle(TelegramClient $telegram): int
    {
        if (! $telegram->configured() || ! filled(config('services.telegram.webhook_secret'))) {
            $this->error('Set TELEGRAM_BOT_TOKEN, TELEGRAM_BOT_USERNAME and TELEGRAM_WEBHOOK_SECRET in .env first.');

            return self::FAILURE;
        }

        $url = route('webhooks.telegram');
        $result = $telegram->call('setWebhook', [
            'url' => $url,
            'secret_token' => config('services.telegram.webhook_secret'),
            'allowed_updates' => ['message', 'callback_query'],
            'drop_pending_updates' => true,
        ]);

        if (! $result?->json('ok')) {
            $this->error('setWebhook failed: '.($result?->json('description') ?? 'no response'));

            return self::FAILURE;
        }
        $this->info('Webhook set to '.$url);

        foreach (['prs' => 'fa', 'ps' => 'ps', 'en' => 'en'] as $locale => $code) {
            app()->setLocale($locale);
            $telegram->call('setMyCommands', [
                'language_code' => $code,
                'commands' => [
                    ['command' => 'next', 'description' => __('app.telegram.cmd_next')],
                    ['command' => 'help', 'description' => __('app.telegram.cmd_help')],
                ],
            ]);
        }
        $this->info('Bot commands set in Dari, Pashto and English.');

        return self::SUCCESS;
    }
}
