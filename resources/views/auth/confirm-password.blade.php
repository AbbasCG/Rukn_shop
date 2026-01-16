<x-guest-layout>
    @php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
    <div dir="{{ $dir }}">
        <!-- Header Section -->
        <div class="mb-8 text-center">
            <a href="{{ route('home') }}" class="inline-block">
                <x-application-logo class="h-12 w-auto text-primary-dark" />
            </a>
            <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('auth.confirm.title') }}</h1>
            <p class="mt-2 text-sm text-primary-dark/60">{{ __('auth.confirm.subtitle') }}</p>
        </div>

        <!-- Info Message -->
        <div class="mb-8 p-4 rounded-lg bg-primary-light border border-primary-gray">
            <p class="text-sm text-primary-dark/80">
                {{ __('auth.confirm.helper') }}
            </p>
        </div>

        <!-- Confirm Password Form -->
        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
            @csrf

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('auth.confirm.password_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                <x-text-input
                    id="password"
                    class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="{{ __('auth.confirm.password_placeholder') }}" />
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
                {{ __('auth.confirm.submit') }}
            </button>

            <!-- Back Button -->
            <div class="text-center">
                <a href="javascript:history.back()" class="text-sm text-primary-dark/70 hover:text-primary-dark transition-colors duration-300">
                    {{ __('auth.confirm.back') }}
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>