<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\Sms\SmsSender;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public const MAX_ATTEMPTS = 5;

    public function __construct(private SmsSender $sms) {}

    public function send(string $phone, string $ip): void
    {
        foreach (['otp-phone:'.$phone => [1, 60], 'otp-phone-hour:'.$phone => [5, 3600], 'otp-ip:'.$ip => [20, 3600]] as $key => [$max, $decay]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                throw ValidationException::withMessages(['phone' => __('app.auth.too_many', ['seconds' => RateLimiter::availableIn($key)])]);
            }
            RateLimiter::hit($key, $decay);
        }

        $code = (string) random_int(100000, 999999);

        OtpCode::where('phone', $phone)->whereNull('used_at')->delete();
        OtpCode::create([
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->sms->send($phone, __('app.auth.sms', ['code' => $code]));
    }

    public function verify(string $phone, string $code): User
    {
        $otp = OtpCode::where('phone', $phone)->whereNull('used_at')->where('expires_at', '>', now())->latest('id')->first();

        if (! $otp || $otp->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages(['code' => __('app.auth.expired')]);
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) {
            throw ValidationException::withMessages(['code' => __('app.auth.wrong_code')]);
        }

        $otp->forceFill(['used_at' => now()])->save();

        $user = User::firstOrNew(['phone' => $phone]);
        $user->phone_verified_at ??= now();
        $user->locale ??= app()->getLocale();
        $user->save();

        return $user;
    }
}
