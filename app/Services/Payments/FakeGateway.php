<?php

namespace App\Services\Payments;

use App\Models\Order;

/**
 * Local/demo gateway: shows a pretend checkout page. Never enabled in production.
 */
class FakeGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'hesabpay';
    }

    public function currency(): string
    {
        return 'AFN';
    }

    public function createCheckout(Order $order): string
    {
        return route('payments.fake', $order->number);
    }
}
