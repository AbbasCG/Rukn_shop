<x-app-layout :title="__('orders.lookup.title')" :noindex="true">
    <div class="max-w-lg mx-auto px-4 sm:px-6 py-16">
        <h1 class="text-3xl font-bold text-primary-dark">{{ __('orders.lookup.title') }}</h1>
        <p class="mt-2 text-neutral-600">{{ __('orders.lookup.subtitle') }}</p>

        <form method="POST" action="{{ r('orders.lookup.submit') }}" class="mt-8 space-y-5 bg-white rounded-2xl shadow-lg p-6">
            @csrf
            <div>
                <label for="lookup-number" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('orders.lookup.number') }}</label>
                <input id="lookup-number" name="number" value="{{ old('number') }}" required maxlength="20" inputmode="numeric" dir="ltr"
                       placeholder="{{ __('orders.lookup.number_placeholder') }}" autocomplete="off"
                       @error('number') aria-invalid="true" aria-describedby="lookup-number-error" @enderror
                       class="w-full rounded-xl border border-neutral-300 px-4 py-3 text-start focus:ring-primary-dark/30 focus:border-primary-dark">
                @error('number')<p id="lookup-number-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="lookup-email" class="block text-xs font-semibold text-neutral-700 mb-1">{{ __('orders.lookup.email') }}</label>
                <input id="lookup-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"
                       @error('email') aria-invalid="true" aria-describedby="lookup-email-error" @enderror
                       class="w-full rounded-xl border border-neutral-300 px-4 py-3 focus:ring-primary-dark/30 focus:border-primary-dark">
                @error('email')<p id="lookup-email-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-primary w-full">{{ __('orders.lookup.submit') }}</button>
        </form>
    </div>
</x-app-layout>
