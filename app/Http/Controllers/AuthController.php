<?php

namespace App\Http\Controllers;

use App\Services\OtpService;
use App\Support\Digits;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $request->validate(['phone' => ['required', 'string', 'max:30']]);
        $phone = Phone::normalize($request->input('phone'));

        if (! $phone) {
            throw ValidationException::withMessages(['phone' => __('app.auth.invalid_phone')]);
        }

        $this->otp->send($phone, (string) $request->ip());
        $request->session()->put('otp_phone', $phone);

        return redirect()->route('login.code');
    }

    public function showCode(Request $request): View|RedirectResponse
    {
        $phone = $request->session()->get('otp_phone');

        return $phone ? view('auth.code', ['phone' => $phone]) : redirect()->route('login');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:12']]);
        $phone = $request->session()->get('otp_phone');

        if (! $phone) {
            return redirect()->route('login');
        }

        $code = preg_replace('/\D/', '', Digits::toLatin($request->input('code')));
        $user = $this->otp->verify($phone, $code);

        Auth::login($user, remember: true);
        $request->session()->forget('otp_phone');
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
