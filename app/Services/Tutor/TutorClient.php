<?php

namespace App\Services\Tutor;

interface TutorClient
{
    /**
     * @param  array<int, array{role:'user'|'assistant', content:string}>  $messages  oldest first, ends with the user's question
     */
    public function reply(string $system, array $messages): TutorReply;
}
