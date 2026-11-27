<x-layouts.app :title="__('app.teach.title')">
    <div class="mx-auto flex max-w-2xl flex-col gap-5 px-4 py-6 lg:px-8">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-extrabold">{{ __('app.teach.title') }}</h1>
            <p class="leading-loose text-slate-600">{{ __('app.teach.intro', ['share' => pct(config('zirak.teacher_share'))]) }}</p>
        </div>

        <ol class="grid gap-3 sm:grid-cols-3">
            @foreach (['step1', 'step2', 'step3'] as $i => $step)
                <li class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm leading-relaxed">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-600 font-bold text-white">{{ n($i + 1) }}</span>{{ __('app.teach.'.$step) }}
                </li>
            @endforeach
        </ol>

        @if (session('status'))
            <div class="rounded-2xl bg-green-50 p-4 text-green-900">{{ session('status') }}</div>
        @endif

        @if ($pending)
            <div class="flex flex-col gap-1 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
                <div class="font-bold">{{ __('app.teach.pending_title') }}</div>
                <div class="text-sm">{{ __('app.teach.pending_text') }}</div>
            </div>
        @else
            @if ($last?->status === 'rejected')
                <div class="flex flex-col gap-1 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="font-bold">{{ __('app.teach.rejected_title') }}</div>
                    @if ($last->admin_note)<div class="text-sm leading-relaxed text-slate-700">{{ $last->admin_note }}</div>@endif
                    <div class="text-sm text-slate-600">{{ __('app.teach.rejected_again') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('teach.submit') }}" class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-5 md:p-6">
                @csrf
                @php($field = 'w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-brand-600 focus:outline-none')
                <label class="flex flex-col gap-1.5">
                    <span class="font-bold">{{ __('app.teach.full_name') }}</span>
                    <input name="full_name" value="{{ old('full_name', auth()->user()->name) }}" required maxlength="120" class="{{ $field }}">
                    @error('full_name')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="flex flex-col gap-1.5">
                    <span class="font-bold">{{ __('app.teach.headline') }}</span>
                    <input name="headline" value="{{ old('headline') }}" required maxlength="120" placeholder="{{ __('app.teach.headline_hint') }}" class="{{ $field }}">
                    @error('headline')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="flex flex-col gap-1.5">
                    <span class="font-bold">{{ __('app.teach.about') }}</span>
                    <textarea name="about" rows="5" required maxlength="2000" placeholder="{{ __('app.teach.about_hint') }}" class="{{ $field }}">{{ old('about') }}</textarea>
                    @error('about')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="flex flex-col gap-1.5">
                    <span class="font-bold">{{ __('app.teach.course_idea') }}</span>
                    <textarea name="course_idea" rows="4" required maxlength="2000" placeholder="{{ __('app.teach.course_idea_hint') }}" class="{{ $field }}">{{ old('course_idea') }}</textarea>
                    @error('course_idea')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
                <fieldset class="flex flex-col gap-2">
                    <legend class="mb-1.5 font-bold">{{ __('app.teach.languages') }}</legend>
                    <div class="flex flex-wrap gap-3">
                        @foreach (['prs' => 'دری', 'ps' => 'پښتو', 'en' => 'English'] as $code => $label)
                            <label class="flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5">
                                <input type="checkbox" name="languages[]" value="{{ $code }}" @checked(in_array($code, old('languages', [app()->getLocale()]), true)) class="h-5 w-5 accent-brand-600">{{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('languages')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                </fieldset>
                <label class="flex flex-col gap-1.5">
                    <span class="font-bold">{{ __('app.teach.sample') }}</span>
                    <input name="sample_url" type="url" dir="ltr" value="{{ old('sample_url') }}" maxlength="500" placeholder="https://youtube.com/..." class="{{ $field }} text-start">
                    <span class="text-sm text-slate-500">{{ __('app.teach.sample_hint') }}</span>
                    @error('sample_url')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
                <button class="rounded-xl bg-brand-600 py-3.5 text-lg font-bold text-white">{{ __('app.teach.send') }}</button>
            </form>
        @endif
    </div>
</x-layouts.app>
