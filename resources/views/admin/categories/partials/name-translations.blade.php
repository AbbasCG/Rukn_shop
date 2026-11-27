{{-- Per-locale category names (stored in categories.name_translations). --}}
<fieldset class="space-y-3">
    <legend class="block text-sm font-medium text-primary-dark">{{ __('admin.categories.form.translations') }}</legend>
    <p class="text-xs text-primary-dark/60">{{ __('admin.categories.form.translations_help') }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @foreach (['nl' => 'Nederlands', 'en' => 'English', 'ar' => 'العربية'] as $code => $language)
            <div>
                <label for="name_translations_{{ $code }}" class="block text-xs font-medium text-primary-dark/70 mb-1">{{ $language }}</label>
                <input
                    id="name_translations_{{ $code }}"
                    name="name_translations[{{ $code }}]"
                    type="text"
                    lang="{{ $code }}"
                    dir="{{ $code === 'ar' ? 'rtl' : 'ltr' }}"
                    value="{{ old("name_translations.$code", ($category ?? null)?->name_translations[$code] ?? '') }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                @error("name_translations.$code")<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</fieldset>
