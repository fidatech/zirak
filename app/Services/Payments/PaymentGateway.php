<?php

namespace App\Services\Payments;

use App\Models\Order;

interface PaymentGateway
{
    /** Short key stored on orders, e.g. "hesabpay". */
    public function key(): string;

    /** ISO currency this gateway charges in. */
    public function currency(): string;

    /** Create a hosted checkout and return the URL to send the student to. */
    public function createCheckout(Order $order): string;
}
