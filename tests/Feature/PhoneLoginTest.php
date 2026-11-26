<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Sms\SmsSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhoneLoginTest extends TestCase
{
    use RefreshDatabase;

    public array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();
        $test = $this;
        $this->app->instance(SmsSender::class, new class($test) implements SmsSender
        {
            public function __construct(private $test) {}

            public function send(string $phone, string $message): void
            {
                $this->test->sent[] = [$phone, $message];
            }
        });
    }

    private function lastCode(): string
    {
        preg_match('/(\d{6})/', end($this->sent)[1], $m);

        return $m[1];
    }

    public function test_student_logs_in_with_phone_and_code(): void
    {
        $this->post('/login', ['phone' => '۰۷۹۱۲۳۴۵۶۷'])->assertRedirect('/login/code');
        $this->assertSame('+93791234567', $this->sent[0][0]);

        $this->post('/login/code', ['code' => $this->lastCode()])->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['phone' => '+93791234567', 'role' => 'student']);
    }

    public function test_wrong_code_is_rejected_and_attempts_are_limited(): void
    {
        $this->post('/login', ['phone' => '0791234567']);
        $good = $this->lastCode();
        $bad = $good === '111111' ? '222222' : '111111';

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login/code', ['code' => $bad])->assertSessionHasErrors('code');
        }
        // Even the right code is refused after 5 wrong tries.
        $this->post('/login/code', ['code' => $good])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_invalid_phone_and_resend_limit(): void
    {
        $this->post('/login', ['phone' => '12'])->assertSessionHasErrors('phone');
        $this->post('/login', ['phone' => '0791234567'])->assertRedirect();
        $this->post('/login', ['phone' => '0791234567'])->assertSessionHasErrors('phone');
        $this->assertCount(1, $this->sent);
    }

    public function test_existing_user_keeps_account(): void
    {
        $user = User::factory()->create(['phone' => '+93791234567']);
        $this->post('/login', ['phone' => '+93 79 123 4567']);
        $this->post('/login/code', ['code' => $this->lastCode()]);
        $this->assertAuthenticatedAs($user);
    }
}
