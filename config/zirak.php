<?php

return [
    'name' => env('APP_NAME', 'Zirak'),

    // Interface languages. "prs" = Dari, "ps" = Pashto.
    'locales' => [
        'prs' => ['label' => 'دری', 'dir' => 'rtl', 'carbon' => 'fa'],
        'ps' => ['label' => 'پښتو', 'dir' => 'rtl', 'carbon' => 'ps'],
        'en' => ['label' => 'English', 'dir' => 'ltr', 'carbon' => 'en'],
    ],

    'default_locale' => env('ZIRAK_DEFAULT_LOCALE', 'prs'),

    // Minimum chapter test score (%) to pass.
    'pass_mark' => (int) env('ZIRAK_PASS_MARK', 70),

    // log = write codes to storage/logs (development). Add a real SMS provider later.
    'sms_driver' => env('SMS_DRIVER', 'log'),

    // AI tutor: answers students' questions from their course's lessons.
    'tutor' => [
        'enabled' => (bool) env('AI_TUTOR_ENABLED', true),
        // anthropic = Claude API, fake = canned answers for local development and tests
        'driver' => env('AI_TUTOR_DRIVER', 'fake'),
        'model' => env('AI_TUTOR_MODEL', 'claude-haiku-5-5'),
        'api_key' => env('ANTHROPIC_API_KEY'),
        'daily_limit' => (int) env('AI_TUTOR_DAILY_LIMIT', 20),
        'max_answer_tokens' => (int) env('AI_TUTOR_MAX_TOKENS', 600),
        'context_chars' => 9000,
    ],
];
