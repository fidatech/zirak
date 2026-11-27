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

    public function showLogin(Request $request): View
    {
        return $this->authPage($request, 'login');
    }

    public function showRegister(Request $request): View
    {
        return $this->authPage($request, 'register');
    }

    /** One page, two tabs. "?as=teacher" sends the person to the teacher application (or /teach) after login. */
    private function authPage(Request $request, string $mode): View
    {
        $as = $request->query('as') === 'teacher' ? 'teacher' : 'student';
        if ($as === 'teacher') {
            $request->session()->put('url.intended', route('teach.apply'));
        } elseif (is_string($next = $request->query('next')) && preg_match('#^/(?![/\\\\])[\w\-./?=&%]*$#', $next)) {
            $request->session()->put('url.intended', url($next));
        }

        return view('auth.login', ['mode' => $mode, 'as' => old('as', $as)]);
    }

    public function sendCode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'mode' => ['nullable', 'in:login,register'],
            'name' => [$request->input('mode') === 'register' ? 'required' : 'nullable', 'string', 'max:80'],
            'as' => ['nullable', 'in:student,teacher'],
        ]);
        $phone = Phone::normalize($request->input('phone'));

        if (! $phone) {
            throw ValidationException::withMessages(['phone' => __('app.auth.invalid_phone')]);
        }

        $this->otp->send($phone, (string) $request->ip());
        $request->session()->put('otp_phone', $phone);
        if (filled($data['name'] ?? null)) {
            $request->session()->put('otp_name', trim(strip_tags($data['name'])));
        }
        if (($data['as'] ?? null) === 'teacher') {
            $request->session()->put('url.intended', route('teach.apply'));
        }

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

        // A name typed on the sign-up form fills an empty name; it never overwrites one.
        if (blank($user->name) && filled($name = $request->session()->get('otp_name'))) {
            $user->forceFill(['name' => $name])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->forget(['otp_phone', 'otp_name']);
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Phones are often shared: remove lessons saved for offline and any cached pages.
        return redirect()->route('home')->header('Clear-Site-Data', '"cache", "storage"');
    }
}
