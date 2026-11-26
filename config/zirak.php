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
];
