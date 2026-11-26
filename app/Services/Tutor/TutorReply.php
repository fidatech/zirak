<?php

namespace App\Services\Tutor;

final class TutorReply
{
    public function __construct(
        public readonly string $text,
        public readonly int $tokensIn = 0,
        public readonly int $tokensOut = 0,
    ) {}
}
