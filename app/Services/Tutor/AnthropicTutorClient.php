<?php

namespace App\Services\Tutor;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Claude Messages API (https://docs.claude.com). */
class AnthropicTutorClient implements TutorClient
{
    public function reply(string $system, array $messages): TutorReply
    {
        $response = Http::baseUrl('https://api.anthropic.com')
            ->withHeaders([
                'x-api-key' => (string) config('zirak.tutor.api_key'),
                'anthropic-version' => '2023-06-01',
            ])
            ->acceptJson()->asJson()->timeout(45)
            ->post('/v1/messages', [
                'model' => config('zirak.tutor.model'),
                'max_tokens' => config('zirak.tutor.max_answer_tokens'),
                'system' => $system,
                'messages' => $messages,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('AI tutor request failed: '.$response->status());
        }

        $text = collect($response->json('content', []))->where('type', 'text')->pluck('text')->implode("\n");

        if (trim($text) === '') {
            throw new RuntimeException('AI tutor returned an empty answer.');
        }

        return new TutorReply(trim($text), (int) $response->json('usage.input_tokens', 0), (int) $response->json('usage.output_tokens', 0));
    }
}
