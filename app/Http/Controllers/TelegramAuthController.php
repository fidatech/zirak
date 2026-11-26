<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramAuthService;
use App\Services\Telegram\TelegramClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TelegramAuthController extends Controller
{
    public function __construct(private TelegramAuthService $auth, private TelegramClient $telegram) {}

    /** Guest: start logging in with Telegram. */
    public function login(Request $request): View
    {
        abort_unless($this->telegram->configured(), 404);
        [, $token] = $this->auth->start('login', null, $request->userAgent());
        $request->session()->put('telegram_token', $token);

        return view('auth.telegram', [
            'link' => $this->auth->deepLink($token),
            'statusUrl' => route('login.telegram.status'),
            'purpose' => 'login',
        ]);
    }

    public function loginStatus(Request $request): JsonResponse
    {
        $result = $this->auth->complete($request->session()->get('telegram_token'));

        if ($result['status'] === 'done') {
            Auth::login($result['user'], remember: true);
            $request->session()->forget('telegram_token');
            $request->session()->regenerate();

            return response()->json(['status' => 'done', 'redirect' => redirect()->intended(route('home'))->getTargetUrl()]);
        }

        return response()->json(['status' => $result['status']]);
    }

    /** Logged in: connect Telegram to this account. */
    public function link(Request $request): View
    {
        abort_unless($this->telegram->configured(), 404);
        [, $token] = $this->auth->start('link', $request->user(), $request->userAgent());
        $request->session()->put('telegram_link_token', $token);

        return view('auth.telegram', [
            'link' => $this->auth->deepLink($token),
            'statusUrl' => route('profile.telegram.status'),
            'purpose' => 'link',
        ]);
    }

    public function linkStatus(Request $request): JsonResponse
    {
        $result = $this->auth->complete($request->session()->get('telegram_link_token'), $request->user());

        if ($result['status'] === 'done') {
            $request->session()->forget('telegram_link_token');
            session()->flash('status', __('app.telegram.linked'));

            return response()->json(['status' => 'done', 'redirect' => route('profile')]);
        }

        return response()->json(['status' => $result['status']]);
    }

    public function unlink(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->phone && ! $user->email) {
            return back()->withErrors(['telegram' => __('app.telegram.only_login')]);
        }

        $user->forceFill(['telegram_id' => null, 'telegram_username' => null, 'telegram_linked_at' => null])->save();

        return back()->with('status', __('app.telegram.unlinked'));
    }

    public function reminders(Request $request): RedirectResponse
    {
        $request->user()->update(['telegram_reminders' => $request->boolean('enabled')]);

        return back()->with('status', __('app.profile.saved'));
    }
}
