<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'nickname' => ['nullable', 'string', 'max:60'],
            'locale' => ['required', Rule::in(array_keys(config('zirak.locales')))],
        ]);

        $data['data_saver'] = $request->boolean('data_saver');
        $request->user()->update($data);
        $request->session()->put('locale', $data['locale']);

        return back()->with('status', __('app.profile.saved'));
    }
}
