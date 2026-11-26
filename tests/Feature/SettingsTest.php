<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\Settings;
use App\Models\Setting;
use App\Models\User;
use App\Services\Settings as SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    public function test_only_admins_can_open_settings(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/settings')->assertForbidden();
        $this->actingAs($this->admin)->get('/admin/settings')->assertOk()->assertSee('Payments (HesabPay)')->assertSee('Telegram');
    }

    public function test_saved_keys_are_encrypted_applied_and_never_shown_again(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(Settings::class)
            ->fillForm([
                'hesabpay_driver' => 'sandbox',
                'hesabpay_api_key' => 'hp-secret-key-9876',
                'bunny_library_id' => '4242',
                'tutor_driver' => 'anthropic',
                'anthropic_api_key' => 'sk-ant-test-ABCD',
                'tutor_daily_limit' => 15,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $row = Setting::where('key', 'hesabpay_api_key')->firstOrFail();
        $this->assertStringNotContainsString('hp-secret', $row->getRawOriginal('value'));
        $this->assertSame('hp-secret-key-9876', Crypt::decryptString($row->getRawOriginal('value')));
        $this->assertSame($this->admin->id, $row->updated_by);

        $this->assertSame('sandbox', config('services.hesabpay.driver'));
        $this->assertSame('https://api-sandbox.hesab.com', config('services.hesabpay.base_url'));
        $this->assertSame('4242', config('services.bunny.library_id'));
        $this->assertSame('sk-ant-test-ABCD', config('zirak.tutor.api_key'));
        $this->assertSame(15, config('zirak.tutor.daily_limit'));

        // A fresh request applies the saved values over .env.
        config(['services.hesabpay.api_key' => null, 'zirak.tutor.daily_limit' => 20]);
        app(SettingsService::class)->apply();
        $this->assertSame('hp-secret-key-9876', config('services.hesabpay.api_key'));
        $this->assertSame(15, config('zirak.tutor.daily_limit'));

        // The page never prints the secret, only its last 4 characters.
        $this->get('/admin/settings')->assertDontSee('hp-secret-key-9876')->assertDontSee('sk-ant-test-ABCD')->assertSee('…9876');

        // Leaving the secret empty keeps it.
        Livewire::test(Settings::class)->fillForm(['hesabpay_api_key' => '', 'bunny_library_id' => '5555'])->call('save');
        $this->assertSame('hp-secret-key-9876', config('services.hesabpay.api_key'));
        $this->assertSame('5555', config('services.bunny.library_id'));
    }

    public function test_emptying_a_normal_field_goes_back_to_env(): void
    {
        config(['services.bunny.library_id' => 'from-env']);
        app(SettingsService::class)->save(['bunny_library_id' => 'from-admin']);
        $this->assertSame('from-admin', config('services.bunny.library_id'));
        app(SettingsService::class)->save(['bunny_library_id' => '']);
        $this->assertDatabaseMissing('settings', ['key' => 'bunny_library_id']);
    }

    public function test_a_value_encrypted_with_another_app_key_is_ignored(): void
    {
        Setting::create(['key' => 'anthropic_api_key', 'value' => 'not-encrypted-with-this-key']);
        config(['zirak.tutor.api_key' => 'from-env']);
        app(SettingsService::class)->refresh();
        $this->assertSame('from-env', config('zirak.tutor.api_key'));
    }

    public function test_connect_telegram_fills_username_and_registers_webhook(): void
    {
        Http::fake([
            'api.telegram.org/*/getMe' => Http::response(['ok' => true, 'result' => ['id' => 9068016278, 'username' => 'ZirakAfBot']]),
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => true]),
        ]);
        $this->actingAs($this->admin);

        Livewire::test(Settings::class)
            ->fillForm(['telegram_bot_token' => '123:FAKE'])
            ->call('connectTelegram')
            ->assertNotified('Telegram bot connected: @ZirakAfBot');

        $this->assertSame('ZirakAfBot', config('services.telegram.bot_username'));
        $this->assertSame(48, strlen(config('services.telegram.webhook_secret')));
        Http::assertSent(fn ($r) => str_contains($r->url(), 'bot123:FAKE/setWebhook') && $r['secret_token'] === config('services.telegram.webhook_secret'));

        // Telegram login now shows on the login page.
        auth()->logout();
        $this->get('/login')->assertSee(__('app.telegram.continue_with'));
    }

    public function test_bad_telegram_token_is_reported(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'Unauthorized'], 401)]);
        $this->actingAs($this->admin);

        Livewire::test(Settings::class)
            ->fillForm(['telegram_bot_token' => '123:WRONG'])
            ->call('connectTelegram')
            ->assertNotified('Telegram did not accept this token');
        $this->assertNull(config('services.telegram.bot_username'));
    }

    public function test_tutor_test_button(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => 'سلام!']], 'usage' => ['input_tokens' => 5, 'output_tokens' => 2]])]);
        $this->actingAs($this->admin);

        Livewire::test(Settings::class)
            ->fillForm(['tutor_driver' => 'anthropic', 'anthropic_api_key' => 'sk-ant-x'])
            ->call('testTutor')
            ->assertNotified('The AI tutor works');
        Http::assertSent(fn ($r) => $r->hasHeader('x-api-key', 'sk-ant-x'));
    }

    public function test_app_still_boots_without_a_database(): void
    {
        // Fresh server before the first migration: the cache table and database are missing.
        Cache::shouldReceive('get')->andThrow(new \RuntimeException('Database file does not exist'));
        config(['services.bunny.library_id' => 'from-env']);

        app(SettingsService::class)->apply();
        $this->assertSame('from-env', config('services.bunny.library_id'));
    }
}
