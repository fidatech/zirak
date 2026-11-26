<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use App\Services\Payments\CheckoutService;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoContentSeeder::class);
        $this->user = User::factory()->create();
        $this->course = Course::where('slug', 'mobile-filmmaking')->firstOrFail(); // 699 AFN
    }

    public function test_fake_gateway_checkout_enrolls_after_payment(): void
    {
        config(['services.hesabpay.driver' => 'fake']);
        $this->actingAs($this->user);

        $response = $this->post(route('courses.purchase', $this->course));
        $order = Order::firstOrFail();
        $response->assertRedirect(route('payments.fake', $order->number));
        $this->assertSame(699, $order->total);
        $this->assertFalse($this->user->isEnrolledIn($this->course));

        $this->post(route('payments.fake.pay', $order->number))->assertRedirect();
        $this->assertTrue($order->fresh()->isPaid());
        $this->assertTrue($this->user->isEnrolledIn($this->course));
        $this->get(route('payments.return', ['order' => $order->number, 'result' => 'success']))->assertOk()->assertSee(__('app.pay.success'));
    }

    public function test_coupon_discounts_and_full_scholarship_skips_gateway(): void
    {
        config(['services.hesabpay.driver' => 'fake']);
        $this->actingAs($this->user);

        $this->post(route('courses.purchase', $this->course), ['coupon' => 'welcome30'])->assertRedirect();
        $order = Order::firstOrFail();
        $this->assertSame(210, $order->discount);
        $this->assertSame(489, $order->total); // 699 - 30% (rounded)

        $this->post(route('courses.purchase', $this->course), ['coupon' => 'SCHOLARSHIP'])->assertRedirect(route('courses.show', $this->course));
        $this->assertTrue($this->user->isEnrolledIn($this->course));
        $this->assertSame(1, Coupon::where('code', 'SCHOLARSHIP')->value('used_count'));
    }

    public function test_invalid_or_used_up_coupon_is_rejected(): void
    {
        Coupon::create(['code' => 'DONE', 'type' => 'percent', 'value' => 50, 'max_uses' => 1, 'used_count' => 1]);
        $this->actingAs($this->user);
        $this->post(route('courses.purchase', $this->course), ['coupon' => 'NOPE'])->assertSessionHasErrors('coupon');
        $this->post(route('courses.purchase', $this->course), ['coupon' => 'DONE'])->assertSessionHasErrors('coupon');
        $this->assertSame(0, Order::count());
    }

    public function test_hesabpay_session_is_created_with_the_api(): void
    {
        config(['services.hesabpay.driver' => 'sandbox', 'services.hesabpay.api_key' => 'test-key', 'services.hesabpay.base_url' => 'https://api-sandbox.hesab.com']);
        Http::fake(['api-sandbox.hesab.com/api/v1/payment/create-session' => Http::response([
            'success' => true, 'session_id' => 'sess-1', 'url' => 'https://checkout.example/pay/sess-1',
        ])]);

        $this->actingAs($this->user)->post(route('courses.purchase', $this->course))->assertRedirect('https://checkout.example/pay/sess-1');

        $order = Order::firstOrFail();
        $this->assertSame('sess-1', $order->gateway_session_id);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'API-KEY test-key')
            && $request['user_id'] === $order->number
            && $request['items'][0]['price'] == 699
            && str_contains($request['redirect_success_url'], $order->number));
    }

    private function pendingOrder(): Order
    {
        return Order::create(['user_id' => $this->user->id, 'course_id' => $this->course->id, 'status' => 'pending', 'gateway' => 'hesabpay', 'currency' => 'AFN', 'subtotal' => 699, 'total' => 699]);
    }

    private function webhook(Order $order, array $overrides = []): array
    {
        return array_merge([
            'status_code' => 10, 'success' => true, 'message' => 'Operation successful',
            'transaction_id' => 'TX-123', 'user_id' => $order->number, 'amount' => 699,
            'signature' => 'sig', 'timestamp' => '1707719607',
        ], $overrides);
    }

    public function test_verified_webhook_marks_order_paid_once(): void
    {
        config(['services.hesabpay.base_url' => 'https://api-sandbox.hesab.com']);
        Http::fake(['api-sandbox.hesab.com/api/v1/hesab/webhooks/verify-signature' => Http::response(['success' => true])]);
        $order = $this->pendingOrder();

        $this->postJson('/webhooks/hesabpay', $this->webhook($order))->assertOk();
        $this->postJson('/webhooks/hesabpay', $this->webhook($order, ['signature' => 'sig2']))->assertOk(); // retry

        $this->assertTrue($order->fresh()->isPaid());
        $this->assertSame('TX-123', $order->fresh()->gateway_transaction_id);
        $this->assertSame(1, $this->user->enrollments()->count());
        $this->assertDatabaseCount('gateway_events', 2);
    }

    public function test_bad_signature_is_rejected(): void
    {
        config(['services.hesabpay.base_url' => 'https://api-sandbox.hesab.com']);
        Http::fake(['*' => Http::response(['success' => false], 400)]);
        $order = $this->pendingOrder();

        $this->postJson('/webhooks/hesabpay', $this->webhook($order))->assertStatus(400);
        $this->assertFalse($order->fresh()->isPaid());
        $this->assertFalse($this->user->isEnrolledIn($this->course));
    }

    public function test_amount_mismatch_does_not_give_access(): void
    {
        config(['services.hesabpay.base_url' => 'https://api-sandbox.hesab.com']);
        Http::fake(['*' => Http::response(['success' => true])]);
        $order = $this->pendingOrder();

        $this->postJson('/webhooks/hesabpay', $this->webhook($order, ['amount' => 1]))->assertOk();
        $this->assertFalse($order->fresh()->isPaid());
    }

    public function test_failure_webhook_never_downgrades_a_paid_order(): void
    {
        config(['services.hesabpay.base_url' => 'https://api-sandbox.hesab.com']);
        Http::fake(['*' => Http::response(['success' => true])]);
        $order = $this->pendingOrder();

        $this->postJson('/webhooks/hesabpay', $this->webhook($order, ['success' => false, 'status' => 'failed', 'status_code' => null, 'reason' => 'payment_declined', 'transaction_id' => null]))->assertOk();
        $this->assertSame('failed', $order->fresh()->status->value);

        $this->postJson('/webhooks/hesabpay', $this->webhook($order))->assertOk();
        $this->postJson('/webhooks/hesabpay', $this->webhook($order, ['success' => false, 'status' => 'failed', 'status_code' => null]))->assertOk();
        $this->assertTrue($order->fresh()->isPaid());
    }

    public function test_fake_gateway_is_refused_in_production(): void
    {
        config(['services.hesabpay.driver' => 'fake']);
        $this->app->detectEnvironment(fn () => 'production');
        $this->expectException(\RuntimeException::class);
        app(CheckoutService::class)->gateway();
    }
}
