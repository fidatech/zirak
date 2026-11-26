<?php

namespace App\Services\Tutor;

/** Canned answers for local development and tests. Never used in production. */
class FakeTutorClient implements TutorClient
{
    public function reply(string $system, array $messages): TutorReply
    {
        $question = end($messages)['content'] ?? '';

        return new TutorReply(__('app.tutor.demo_answer', ['question' => mb_substr($question, 0, 120)]), mb_strlen($system), 40);
    }
}
