<?php

namespace App\Filament\Pages;

use App\Services\Settings as SettingsService;
use App\Services\Telegram\TelegramClient;
use App\Services\Tutor\AnthropicTutorClient;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Throwable;

/** Admin → Settings: payment, video, AI tutor and Telegram keys, stored encrypted in the database. */
class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'Settings';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    private function settings(): SettingsService
    {
        return app(SettingsService::class);
    }

    public function mount(): void
    {
        $s = $this->settings();
        $this->form->fill([
            'hesabpay_driver' => $s->get('hesabpay_driver'),
            'bunny_library_id' => $s->get('bunny_library_id'),
            'bunny_api_key' => null,
            'bunny_read_only_key' => null,
            'tutor_enabled' => (bool) $s->get('tutor_enabled'),
            'tutor_driver' => $s->get('tutor_driver'),
            'tutor_model' => $s->get('tutor_model'),
            'tutor_daily_limit' => $s->get('tutor_daily_limit'),
            'telegram_bot_username' => $s->get('telegram_bot_username'),
            'plan_enabled' => (bool) $s->get('plan_enabled'),
            'plan_price_afn' => $s->get('plan_price_afn'),
            'plan_teacher_share' => $s->get('plan_teacher_share'),
            // Secrets are never sent back to the browser.
            'hesabpay_api_key' => null,
            'anthropic_api_key' => null,
            'telegram_bot_token' => null,
        ]);
    }

    private function secret(string $key, string $label): TextInput
    {
        $hint = $this->settings()->hint($key);

        return TextInput::make($key)->label($label)->password()->revealable()->autocomplete('new-password')
            ->placeholder($hint ? "Saved ({$hint}). Leave empty to keep it." : 'Not set')
            ->helperText($this->settings()->isSaved($key) ? 'Saved here in admin (encrypted).' : ($hint ? 'Coming from the server .env file.' : null));
    }

    public function form(Schema $schema): Schema
    {
        $production = app()->environment('production');

        return $schema->statePath('data')->components([
            Section::make('Payments (HesabPay)')
                ->description('Use "sandbox" with test keys first. Switch to "live" only when HesabPay gives you live keys.')
                ->schema([
                    Select::make('hesabpay_driver')->label('Mode')->required()->native(false)
                        ->options(array_filter([
                            'fake' => $production ? null : 'Demo checkout (local testing only)',
                            'sandbox' => 'Sandbox (test payments)',
                            'live' => 'Live (real money)',
                        ])),
                    $this->secret('hesabpay_api_key', 'HesabPay API key'),
                ]),
            Section::make('Zirak Plus (monthly plan)')
                ->description('One price opens every course for '.config('zirak.subscription.days').' days. Students pay again to add more time; HesabPay has no automatic charging.')
                ->schema([
                    Toggle::make('plan_enabled')->label('Sell Zirak Plus'),
                    TextInput::make('plan_price_afn')->label('Price per period (AFN)')->numeric()->integer()->minValue(10)->maxValue(1_000_000)->required()->suffix('AFN'),
                    TextInput::make('plan_teacher_share')->label('Teachers\' share of plan money (%)')->numeric()->integer()->minValue(0)->maxValue(100)->required()->suffix('%')
                        ->helperText('Split each month between teachers by lessons that subscribers completed in their courses.'),
                ]),
            Section::make('Videos (Bunny Stream)')
                ->schema([
                    TextInput::make('bunny_library_id')->label('Video library ID')->maxLength(40)
                        ->helperText('Bunny → Stream → your library → API.'),
                    $this->secret('bunny_api_key', 'Library API key (for teacher video uploads)'),
                    $this->secret('bunny_read_only_key', 'Library read-only API key (checks Bunny\'s webhook)'),
                    Placeholder::make('bunny_webhook')->label('Webhook URL to paste in Bunny → Stream → library → Webhook')
                        ->content(fn () => url('/webhooks/bunny')),
                ]),
            Section::make('AI tutor')
                ->schema([
                    Toggle::make('tutor_enabled')->label('AI tutor is on'),
                    Select::make('tutor_driver')->label('Answers come from')->required()->native(false)
                        ->options(array_filter([
                            'anthropic' => 'Claude (Anthropic API)',
                            'fake' => $production ? null : 'Sample answers (local testing only)',
                        ])),
                    $this->secret('anthropic_api_key', 'Anthropic API key'),
                    TextInput::make('tutor_model')->label('Model')->required()->maxLength(60),
                    TextInput::make('tutor_daily_limit')->label('Questions per student per day')->numeric()->integer()->minValue(1)->maxValue(500)->required(),
                    Actions::make([
                        Action::make('testTutor')->label('Save and send a test question')->color('gray')
                            ->action(fn () => $this->testTutor()),
                    ]),
                ]),
            Section::make('Telegram')
                ->description('Create a bot with @BotFather, paste its token, then press "Save and connect bot".')
                ->schema([
                    $this->secret('telegram_bot_token', 'Bot token'),
                    TextInput::make('telegram_bot_username')->label('Bot username')->prefix('@')->disabled()->dehydrated(false)
                        ->helperText('Filled in automatically when you connect the bot.'),
                    Actions::make([
                        Action::make('connectTelegram')->label('Save and connect bot')->color('gray')
                            ->action(fn () => $this->connectTelegram()),
                    ]),
                ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([Actions::make([Action::make('save')->label('Save settings')->submit('save')])]),
        ]);
    }

    public function save(bool $notify = true): void
    {
        $data = $this->form->getState();
        $this->settings()->save($data, auth()->user());
        $this->mount();

        if ($notify) {
            Notification::make()->title('Settings saved')->success()->send();
        }
    }

    public function testTutor(): void
    {
        $this->save(false);
        if (config('zirak.tutor.driver') !== 'anthropic' || blank(config('zirak.tutor.api_key'))) {
            Notification::make()->title('Choose Claude and add an Anthropic API key first')->warning()->send();

            return;
        }
        try {
            $reply = app(AnthropicTutorClient::class)->reply('Answer in one short sentence.', [['role' => 'user', 'content' => 'Say hello in Dari.']]);
            Notification::make()->title('The AI tutor works')->body(Str::limit($reply->text, 200))->success()->send();
        } catch (Throwable $e) {
            report($e);
            Notification::make()->title('The AI tutor did not answer')->body(Str::limit($e->getMessage(), 200))->danger()->persistent()->send();
        }
    }

    public function connectTelegram(): void
    {
        $this->save(false);
        $telegram = app(TelegramClient::class);

        if (blank(config('services.telegram.bot_token'))) {
            Notification::make()->title('Paste the bot token first')->warning()->send();

            return;
        }

        $me = $telegram->call('getMe');
        if (! $me?->json('ok')) {
            Notification::make()->title('Telegram did not accept this token')->body((string) $me?->json('description'))->danger()->persistent()->send();

            return;
        }

        $values = ['telegram_bot_username' => $me->json('result.username')];
        if (blank(config('services.telegram.webhook_secret'))) {
            $values['telegram_webhook_secret'] = Str::random(48);
        }
        $this->settings()->save($values, auth()->user());

        $ok = Artisan::call('zirak:telegram-setup') === 0;
        $this->mount();

        Notification::make()
            ->title($ok ? 'Telegram bot connected: @'.$me->json('result.username') : 'Could not register the webhook')
            ->body(trim(Artisan::output()))
            ->{$ok ? 'success' : 'danger'}()
            ->send();
    }
}
