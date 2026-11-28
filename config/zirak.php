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
    // Teacher's share of each sale, in percent (can be changed per course in admin).
    'teacher_share' => (int) env('ZIRAK_TEACHER_SHARE', 70),

    // Zirak Plus: one monthly price opens every course while it lasts. HesabPay has no automatic
    // charging, so students pay again to add another period. Teachers share plan money by lessons studied.
    'subscription' => [
        'enabled' => (bool) env('ZIRAK_PLAN_ENABLED', true),
        'price_afn' => (int) env('ZIRAK_PLAN_PRICE_AFN', 999),
        'days' => (int) env('ZIRAK_PLAN_DAYS', 30),
        // Teachers' part of plan money (percent), split by lessons completed by subscribers in their courses.
        'teacher_share' => (int) env('ZIRAK_PLAN_TEACHER_SHARE', 70),
    ],

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
