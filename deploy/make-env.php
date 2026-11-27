<?php

// Builds the first production .env from .env.example and the DB_* deploy secrets (read from the environment).
// Used by the deploy only when the server has no .env yet; an existing .env is never overwritten.

$example = file_get_contents($argv[1] ?? '.env.example');

$quote = function (string $value): string {
    if ($value === '' || preg_match('/^[A-Za-z0-9_.\/:@+-]+$/', $value)) {
        return $value;
    }
    if (! str_contains($value, "'")) {
        return "'".$value."'"; // single quotes: no ${} expansion
    }

    return '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
};

$set = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_URL' => getenv('D_SITE') ?: 'https://zirak.af',
    'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
    'LOG_LEVEL' => 'warning',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => getenv('D_DB_HOST') ?: 'localhost',
    'DB_PORT' => '3306',
    'DB_DATABASE' => (string) getenv('D_DB_NAME'),
    'DB_USERNAME' => (string) getenv('D_DB_USER'),
    'DB_PASSWORD' => (string) getenv('D_DB_PASS'),
    'SESSION_SECURE_COOKIE' => 'true',
];

$lines = preg_split('/\R/', $example);
$seen = [];
foreach ($lines as $i => $line) {
    // "# DB_HOST=..." lines in the example are commented-out defaults: switch them on.
    if (preg_match('/^#?\s*([A-Z0-9_]+)=/', $line, $m) && array_key_exists($m[1], $set) && ! isset($seen[$m[1]])) {
        $lines[$i] = $m[1].'='.$quote($set[$m[1]]);
        $seen[$m[1]] = true;
    }
}
foreach ($set as $key => $value) {
    if (! isset($seen[$key])) {
        $lines[] = $key.'='.$quote($value);
    }
}

echo implode("\n", $lines)."\n";
