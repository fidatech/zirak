{{-- "Find your level": a GET form to /levels?from=N (no account needed). --}}
<form method="GET" action="{{ route('levels') }}#levels" class="flex w-full flex-col gap-4 rounded-2xl bg-white p-6 text-ink shadow-[0_20px_50px_-20px_rgba(0,0,0,.35)] sm:p-7">
    <div class="text-xl font-extrabold">{{ __('app.levels.find_title') }}</div>
    <fieldset class="flex flex-col gap-2">
        <legend class="mb-2 text-sm font-bold">{{ __('app.levels.find_q') }}</legend>
        @foreach ([1, 2, 3, 4] as $i)
            <label class="flex cursor-pointer items-center gap-3 rounded-xl border-[1.5px] border-line bg-white px-3.5 py-3 text-[15px] transition hover:border-brand-300 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                <input type="radio" name="from" value="{{ $i }}" @checked(($selected ?? 1) === $i) class="h-4 w-4 accent-brand-600">{{ __('app.levels.find_'.$i) }}
            </label>
        @endforeach
    </fieldset>
    <button class="btn-accent min-h-13 rounded-xl text-base font-extrabold">{{ __('app.levels.find_btn') }}</button>
    <p class="text-xs text-muted">{{ __('app.levels.find_note') }}</p>
</form>
