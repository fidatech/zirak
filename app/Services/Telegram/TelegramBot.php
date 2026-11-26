<?php

namespace App\Services\Telegram;

use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Support\Facades\App;

/** Handles updates sent by Telegram to the webhook. */
class TelegramBot
{
    public function __construct(
        private TelegramClient $telegram,
        private TelegramAuthService $auth,
        private ProgressService $progress,
    ) {}

    public function handle(array $update): void
    {
        if (isset($update['callback_query'])) {
            $this->callback($update['callback_query']);

            return;
        }

        $message = $update['message'] ?? null;
        if (! $message || ($message['chat']['type'] ?? '') !== 'private' || ! isset($message['from']['id'])) {
            return; // only private chats with the bot
        }

        $from = $message['from'];
        $chatId = $message['chat']['id'];
        $user = User::where('telegram_id', $from['id'])->first();
        App::setLocale($user?->locale ?? TelegramClient::locale($from['language_code'] ?? null));

        $text = trim($message['text'] ?? '');
        [$command, $payload] = array_pad(explode(' ', $text, 2), 2, '');
        $command = strtolower(explode('@', $command)[0]);

        match (true) {
            $command === '/start' && str_starts_with($payload, 'login_') => $this->auth->received(substr($payload, 6), $from, $chatId),
            $command === '/start' => $this->welcome($chatId, $user),
            $command === '/next' => $this->next($chatId, $user),
            default => $this->help($chatId, $user),
        };
    }

    private function callback(array $query): void
    {
        $from = $query['from'] ?? [];
        $data = (string) ($query['data'] ?? '');
        App::setLocale(TelegramClient::locale($from['language_code'] ?? null));

        if (! preg_match('/^tg_(ok|no):(\d+)$/', $data, $m)) {
            $this->telegram->answerCallback($query['id']);

            return;
        }

        $login = $this->auth->answer((int) $m[2], $m[1] === 'ok', $from['id'] ?? 0);
        $text = match (true) {
            $login === null => __('app.telegram.expired'),
            $login->status === 'confirmed' => __('app.telegram.confirmed'),
            default => __('app.telegram.cancelled'),
        };

        $this->telegram->answerCallback($query['id']);
        if (isset($query['message']['chat']['id'], $query['message']['message_id'])) {
            $this->telegram->edit($query['message']['chat']['id'], $query['message']['message_id'], $text);
        }
    }

    private function welcome(int|string $chatId, ?User $user): void
    {
        $text = __('app.telegram.welcome')."\n\n".($user ? __('app.telegram.commands') : __('app.telegram.not_linked'));
        $this->telegram->send($chatId, $text, [[['text' => __('app.telegram.open_site'), 'url' => route('home')]]]);
    }

    private function help(int|string $chatId, ?User $user): void
    {
        $this->telegram->send($chatId, __('app.telegram.commands'), [[['text' => __('app.telegram.open_site'), 'url' => route('home')]]]);
    }

    private function next(int|string $chatId, ?User $user): void
    {
        if (! $user) {
            $this->telegram->send($chatId, __('app.telegram.not_linked'), [[['text' => __('app.telegram.open_site'), 'url' => route('login')]]]);

            return;
        }

        $step = $this->nextStep($user);
        if (! $step) {
            $this->telegram->send($chatId, __('app.telegram.no_course'), [[['text' => __('app.home.browse'), 'url' => route('catalog')]]]);

            return;
        }

        $this->telegram->send($chatId, $step['text'], [[['text' => __('app.course.continue'), 'url' => $step['url']]]]);
    }

    /** @return array{text:string,url:string}|null */
    public function nextStep(User $user): ?array
    {
        foreach ($user->courses()->latest('enrollments.updated_at')->get() as $course) {
            $state = $this->progress->courseState($user, $course);
            if ($state['next']) {
                $next = $state['next'];

                return [
                    'text' => __('app.telegram.next_step', [
                        'course' => $course->title,
                        'chapter' => $next['chapter']->title,
                        'step' => __('app.chapter.'.$next['tab']),
                        'percent' => pct($state['percent']),
                    ]),
                    'url' => route('chapters.show', [$course, $next['chapter'], $next['tab']]),
                ];
            }
        }

        return null;
    }
}
