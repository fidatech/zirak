<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/** Writes SMS messages to the log. For local development and until an SMS provider is chosen. */
class LogSmsSender implements SmsSender
{
    public function send(string $phone, string $message): void
    {
        Log::info('SMS to '.$phone.': '.$message);
    }
}
