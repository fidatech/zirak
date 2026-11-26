<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\GatewayEvent;
use App\Models\Order;
use App\Services\EnrollmentService;
use App\Services\Payments\HesabPayGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /** Where HesabPay sends the student back. Never grants access by itself; the webhook does. */
    public function return(Request $request): View
    {
        $order = Order::where('number', $request->query('order'))->where('user_id', $request->user()->id)->with('course')->firstOrFail();

        $status = $order->isPaid() ? 'success' : ($request->query('result') === 'failure' ? 'failed' : 'pending');

        return view('payments.result', compact('order', 'status'));
    }

    /** Demo checkout used when HESABPAY_DRIVER=fake (local/testing only). */
    public function fake(Request $request, string $number): View
    {
        abort_unless(config('services.hesabpay.driver') === 'fake' && ! app()->environment('production'), 404);
        $order = Order::where('number', $number)->where('user_id', $request->user()->id)->with('course')->firstOrFail();

        return view('payments.fake', compact('order'));
    }

    public function fakePay(Request $request, string $number, EnrollmentService $enrollments): RedirectResponse
    {
        abort_unless(config('services.hesabpay.driver') === 'fake' && ! app()->environment('production'), 404);
        $order = Order::where('number', $number)->where('user_id', $request->user()->id)->firstOrFail();

        if ($request->boolean('cancel')) {
            return redirect()->route('payments.return', ['order' => $order->number, 'result' => 'failure']);
        }

        $enrollments->markPaid($order, 'FAKE-'.$order->number);

        return redirect()->route('payments.return', ['order' => $order->number, 'result' => 'success']);
    }

    /** HesabPay webhook: verify with HesabPay, then update the order once. */
    public function hesabpayWebhook(Request $request, HesabPayGateway $gateway, EnrollmentService $enrollments): JsonResponse
    {
        $payload = $request->json()->all() ?: $request->all();
        $reference = $payload['user_id'] ?? null;

        $event = GatewayEvent::create(['gateway' => 'hesabpay', 'reference' => $reference, 'payload' => $payload]);

        $verified = $gateway->verifySignature($payload['signature'] ?? null, isset($payload['timestamp']) ? (string) $payload['timestamp'] : null);
        $event->update(['verified' => $verified]);

        if (! $verified) {
            $event->update(['result' => 'rejected_signature']);
            Log::warning('HesabPay webhook rejected: bad signature', ['reference' => $reference]);

            return response()->json(['ok' => false], 400);
        }

        $order = $reference ? Order::where('number', $reference)->first() : null;
        if (! $order) {
            $event->update(['result' => 'unknown_order']);

            return response()->json(['ok' => true]);
        }

        $success = ($payload['success'] ?? false) === true && (int) ($payload['status_code'] ?? 0) === 10;

        if ($success) {
            if (abs((float) ($payload['amount'] ?? 0) - $order->amountMajor()) > 0.01) {
                $event->update(['result' => 'amount_mismatch']);
                Log::error('HesabPay amount mismatch', ['order' => $order->number, 'payload_amount' => $payload['amount'] ?? null]);

                return response()->json(['ok' => true]);
            }

            $enrollments->markPaid($order, $payload['transaction_id'] ?? null);
            $event->update(['result' => 'paid']);

            return response()->json(['ok' => true]);
        }

        // Failure: never downgrade an order that is already paid.
        if (! $order->isPaid()) {
            $order->update(['status' => OrderStatus::Failed, 'failure_reason' => $payload['reason'] ?? 'payment_declined']);
        }
        $event->update(['result' => 'failed']);

        return response()->json(['ok' => true]);
    }
}
