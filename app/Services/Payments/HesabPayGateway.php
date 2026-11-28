<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * HesabPay hosted checkout (AFN).
 * Docs: https://docs.hesab.com — create-session, webhooks/verify-signature.
 */
class HesabPayGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'hesabpay';
    }

    public function currency(): string
    {
        return 'AFN';
    }

    public function baseUrl(): string
    {
        return rtrim((string) config('services.hesabpay.base_url'), '/');
    }

    private function client()
    {
        return Http::baseUrl($this->baseUrl())
            ->withHeaders(['Authorization' => 'API-KEY '.config('services.hesabpay.api_key')])
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }

    public function createCheckout(Order $order): string
    {
        $order->loadMissing('course', 'user');

        $response = $this->client()->post('/api/v1/payment/create-session', array_filter([
            'email' => $order->user->email,
            'user_id' => $order->number,
            'items' => [[
                'id' => $order->number,
                'name' => mb_substr($order->itemName(), 0, 450),
                'price' => $order->amountMajor(),
            ]],
            'redirect_success_url' => route('payments.return', ['order' => $order->number, 'result' => 'success']),
            'redirect_failure_url' => route('payments.return', ['order' => $order->number, 'result' => 'failure']),
        ]));

        if (! $response->successful() || ! $response->json('url')) {
            throw new RuntimeException('HesabPay session failed: '.$response->status().' '.$response->body());
        }

        $order->forceFill(['gateway_session_id' => $response->json('session_id')])->save();

        return $response->json('url');
    }

    /** Ask HesabPay whether a webhook's signature + timestamp are genuine. */
    public function verifySignature(?string $signature, ?string $timestamp): bool
    {
        if (! $signature || ! $timestamp) {
            return false;
        }

        $response = $this->client()->post('/api/v1/hesab/webhooks/verify-signature', [
            'signature' => $signature,
            'timestamp' => $timestamp,
        ]);

        return $response->successful() && $response->json('success') === true;
    }
}
