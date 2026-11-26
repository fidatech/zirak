<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramBot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramBot $bot): JsonResponse
    {
        $secret = (string) config('services.telegram.webhook_secret');
        abort_unless($secret !== '' && hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token')), 403);

        try {
            $bot->handle($request->json()->all());
        } catch (Throwable $e) {
            // Always answer 200 so Telegram does not retry the same update forever.
            Log::error('Telegram update failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }
}
