<?php

namespace App\Console\Commands;

use App\Models\LessonCompletion;
use App\Models\PracticeAnswer;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\Telegram\TelegramBot;
use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

#[Signature('zirak:telegram-reminders')]
#[Description('Send a daily "your next step" reminder on Telegram to students who have not studied today')]
class TelegramReminders extends Command
{
    public function handle(TelegramClient $telegram, TelegramBot $bot): int
    {
        if (! $telegram->configured()) {
            return self::SUCCESS;
        }

        $sent = 0;
        User::whereNotNull('telegram_id')->where('telegram_reminders', true)
            ->where(fn ($q) => $q->whereNull('telegram_reminded_at')->orWhere('telegram_reminded_at', '<', today()))
            ->whereHas('enrollments', fn ($q) => $q->whereNull('completed_at'))
            ->chunkById(100, function ($users) use ($telegram, $bot, &$sent) {
                foreach ($users as $user) {
                    if ($this->studiedToday($user)) {
                        continue;
                    }
                    App::setLocale($user->locale ?: config('zirak.default_locale'));
                    $step = $bot->nextStep($user);
                    if (! $step) {
                        continue;
                    }

                    $response = $telegram->send($user->telegram_id, __('app.telegram.reminder')."\n\n".$step['text'], [
                        [['text' => __('app.course.continue'), 'url' => $step['url']]],
                    ]);

                    // 403 = the student blocked the bot: stop reminding them.
                    if ($response?->status() === 403) {
                        $user->forceFill(['telegram_reminders' => false])->save();

                        continue;
                    }

                    $user->forceFill(['telegram_reminded_at' => now()])->save();
                    $sent++;
                    usleep(50_000); // stay far below Telegram's rate limits
                }
            });

        $this->info("Reminders sent: {$sent}");

        return self::SUCCESS;
    }

    private function studiedToday(User $user): bool
    {
        return LessonCompletion::where('user_id', $user->id)->where('completed_at', '>=', today())->exists()
            || PracticeAnswer::where('user_id', $user->id)->where('updated_at', '>=', today())->exists()
            || TestAttempt::where('user_id', $user->id)->where('created_at', '>=', today())->exists();
    }
}
