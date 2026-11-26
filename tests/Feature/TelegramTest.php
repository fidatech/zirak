<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LessonCompletion;
use App\Models\TelegramLogin;
use App\Models\User;
use App\Services\EnrollmentService;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramTest extends TestCase
{
    use RefreshDatabase;

    private int $telegramStatus = 200;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.telegram.bot_token' => '123:ABC',
            'services.telegram.bot_username' => 'ZirakBot',
            'services.telegram.webhook_secret' => 'hook-secret',
        ]);
        Http::fake(['api.telegram.org/*' => fn () => $this->telegramStatus === 200
            ? Http::response(['ok' => true, 'result' => []])
            : Http::response(['ok' => false, 'description' => 'Forbidden: bot was blocked by the user'], $this->telegramStatus)]);
    }

    private function hook(array $update)
    {
        return $this->postJson('/webhooks/telegram', $update, ['X-Telegram-Bot-Api-Secret-Token' => 'hook-secret']);
    }

    private function start(string $token, int $fromId = 555, string $lang = 'fa')
    {
        return $this->hook(['update_id' => 1, 'message' => [
            'message_id' => 10, 'text' => '/start login_'.$token,
            'chat' => ['id' => $fromId, 'type' => 'private'],
            'from' => ['id' => $fromId, 'first_name' => 'Maryam', 'username' => 'maryam_af', 'language_code' => $lang],
        ]]);
    }

    private function press(TelegramLogin $login, bool $yes, int $fromId = 555)
    {
        return $this->hook(['update_id' => 2, 'callback_query' => [
            'id' => 'cb1', 'data' => ($yes ? 'tg_ok:' : 'tg_no:').$login->id,
            'from' => ['id' => $fromId, 'language_code' => 'fa'],
            'message' => ['message_id' => 11, 'chat' => ['id' => $fromId]],
        ]]);
    }

    private function tokenFromSession(): string
    {
        return session('telegram_token') ?? session('telegram_link_token');
    }

    public function test_full_login_with_telegram(): void
    {
        $this->get('/login')->assertSee(__('app.telegram.continue_with'));
        $this->get('/login/telegram')->assertOk()->assertSee('https://t.me/ZirakBot?start=login_', false);
        $token = $this->tokenFromSession();

        $this->getJson('/login/telegram/status')->assertJson(['status' => 'pending']);

        $this->start($token)->assertOk();
        Http::assertSent(fn ($r) => str_contains($r->url(), '/sendMessage') && str_contains(json_encode($r['reply_markup']), 'tg_ok:'));

        $login = TelegramLogin::firstOrFail();
        $this->press($login, true, 999)->assertOk(); // someone else's tap is ignored
        $this->assertSame('pending', $login->fresh()->status);

        $this->press($login, true)->assertOk();
        $this->assertSame('confirmed', $login->fresh()->status);

        $this->getJson('/login/telegram/status')->assertJson(['status' => 'done']);
        $this->assertAuthenticated();
        $user = User::firstOrFail();
        $this->assertSame(555, (int) $user->telegram_id);
        $this->assertSame('maryam_af', $user->telegram_username);
        $this->assertNull($user->phone);
        $this->assertSame('used', $login->fresh()->status);
    }

    public function test_a_confirmed_token_only_logs_in_the_browser_that_started_it(): void
    {
        $this->get('/login/telegram');
        $token = $this->tokenFromSession();
        $this->start($token);
        $this->press(TelegramLogin::firstOrFail(), true);

        // A different browser (fresh session) polling cannot use it.
        $this->flushSession();
        $this->getJson('/login/telegram/status')->assertJson(['status' => 'expired']);
        $this->assertGuest();
    }

    public function test_cancel_and_expiry(): void
    {
        $this->get('/login/telegram');
        $this->start($this->tokenFromSession());
        $this->press(TelegramLogin::firstOrFail(), false);
        $this->getJson('/login/telegram/status')->assertJson(['status' => 'cancelled']);

        $this->get('/login/telegram');
        $token = $this->tokenFromSession();
        TelegramLogin::latest('id')->first()->update(['expires_at' => now()->subMinute()]);
        $this->start($token);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/sendMessage') && $r['text'] === __('app.telegram.expired'));
        $this->getJson('/login/telegram/status')->assertJson(['status' => 'expired']);
        $this->assertGuest();
    }

    public function test_existing_telegram_user_logs_back_into_same_account(): void
    {
        $user = User::factory()->create(['telegram_id' => 555]);
        $this->get('/login/telegram');
        $this->start($this->tokenFromSession());
        $this->press(TelegramLogin::firstOrFail(), true);
        $this->getJson('/login/telegram/status')->assertJson(['status' => 'done']);
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
    }

    public function test_link_telegram_from_profile_and_unlink(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile')->assertSee(__('app.telegram.connect'));
        $this->get('/profile/telegram')->assertOk();
        $this->start($this->tokenFromSession(), 777);
        $this->press(TelegramLogin::firstOrFail(), true, 777);
        $this->getJson('/profile/telegram/status')->assertJson(['status' => 'done']);
        $this->assertSame(777, (int) $user->fresh()->telegram_id);

        $this->put('/profile/telegram/reminders', ['enabled' => 0])->assertRedirect();
        $this->assertFalse($user->fresh()->telegram_reminders);

        $this->delete('/profile/telegram')->assertRedirect();
        $this->assertNull($user->fresh()->telegram_id);
    }

    public function test_cannot_link_a_telegram_account_used_by_someone_else(): void
    {
        User::factory()->create(['telegram_id' => 777]);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile/telegram');
        $this->start($this->tokenFromSession(), 777);
        $this->press(TelegramLogin::firstOrFail(), true, 777);
        $this->getJson('/profile/telegram/status')->assertStatus(422);
        $this->assertNull($user->fresh()->telegram_id);
    }

    public function test_telegram_only_user_cannot_unlink(): void
    {
        $user = User::factory()->create(['phone' => null, 'email' => null, 'telegram_id' => 1]);
        $this->actingAs($user)->delete('/profile/telegram')->assertSessionHasErrors('telegram');
        $this->assertSame(1, (int) $user->fresh()->telegram_id);
    }

    public function test_webhook_requires_the_secret(): void
    {
        $this->postJson('/webhooks/telegram', ['update_id' => 1])->assertForbidden();
        $this->postJson('/webhooks/telegram', ['update_id' => 1], ['X-Telegram-Bot-Api-Secret-Token' => 'wrong'])->assertForbidden();
        config(['services.telegram.webhook_secret' => null]);
        $this->postJson('/webhooks/telegram', ['update_id' => 1], ['X-Telegram-Bot-Api-Secret-Token' => ''])->assertForbidden();
    }

    public function test_next_command_and_reminders(): void
    {
        $this->seed(DemoContentSeeder::class);
        $course = Course::where('slug', 'ai-beginner')->firstOrFail();
        $linked = User::factory()->create(['telegram_id' => 4242, 'locale' => 'ps']);
        $optedOut = User::factory()->create(['telegram_id' => 4343, 'telegram_reminders' => false]);
        $studied = User::factory()->create(['telegram_id' => 4444]);
        foreach ([$linked, $optedOut, $studied] as $u) {
            app(EnrollmentService::class)->enroll($u, $course, 'free');
        }
        LessonCompletion::create(['user_id' => $studied->id, 'lesson_id' => $course->chapters->first()->lessons->first()->id, 'completed_at' => now()]);

        $this->hook(['update_id' => 3, 'message' => ['message_id' => 1, 'text' => '/next', 'chat' => ['id' => 4242, 'type' => 'private'], 'from' => ['id' => 4242, 'language_code' => 'ps']]])->assertOk();
        Http::assertSent(fn ($r) => str_contains($r->url(), '/sendMessage') && $r['chat_id'] === 4242 && str_contains($r['text'], $course->title)
            && str_contains(json_encode($r['reply_markup']), '\/chapters\/'));

        $this->artisan('zirak:telegram-reminders')->expectsOutput('Reminders sent: 1')->assertSuccessful();
        $this->assertNotNull($linked->fresh()->telegram_reminded_at);
        $this->assertNull($optedOut->fresh()->telegram_reminded_at);
        $this->assertNull($studied->fresh()->telegram_reminded_at);

        // Only once a day.
        $this->artisan('zirak:telegram-reminders')->expectsOutput('Reminders sent: 0');
    }

    public function test_reminders_stop_when_the_student_blocked_the_bot(): void
    {
        $this->seed(DemoContentSeeder::class);
        $this->telegramStatus = 403;
        $user = User::factory()->create(['telegram_id' => 4242]);
        app(EnrollmentService::class)->enroll($user, Course::where('slug', 'ai-beginner')->firstOrFail(), 'free');

        $this->artisan('zirak:telegram-reminders')->assertSuccessful();
        $this->assertFalse($user->fresh()->telegram_reminders);
    }

    public function test_setup_command_registers_webhook(): void
    {
        $this->artisan('zirak:telegram-setup')->assertSuccessful();
        Http::assertSent(fn ($r) => str_contains($r->url(), '/setWebhook') && $r['secret_token'] === 'hook-secret' && str_ends_with($r['url'], '/webhooks/telegram'));
        Http::assertSent(fn ($r) => str_contains($r->url(), '/setMyCommands') && $r['language_code'] === 'ps');
    }

    public function test_telegram_is_hidden_when_not_configured(): void
    {
        config(['services.telegram.bot_token' => null]);
        $this->get('/login')->assertDontSee(__('app.telegram.continue_with'));
        $this->get('/login/telegram')->assertNotFound();
    }
}
