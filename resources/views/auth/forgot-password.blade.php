<x-guest-layout>
    @php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
    <div dir="{{ $dir }}">
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Header Section -->
        <div class="mb-8 text-center">
            <a href="{{ route('home') }}" class="inline-block">
                <x-application-logo class="h-12 w-auto text-primary-dark" />
            </a>
            <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('auth.forgot.title') }}</h1>
            <p class="mt-2 text-sm text-primary-dark/60">{{ __('auth.forgot.subtitle') }}</p>
        </div>

        <!-- Info Message -->
        <div class="mb-8 p-4 rounded-lg bg-primary-light border border-primary-gray">
            <p class="text-sm text-primary-dark/80">
                {{ __('auth.forgot.helper') }}
            </p>
        </div>

        <!-- Password Reset Form -->
        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('auth.forgot.email_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                <x-text-input
                    id="email"
                    class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    placeholder="{{ __('auth.forgot.email_placeholder') }}" />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
                {{ __('auth.forgot.submit') }}
            </button>

            <!-- Back to Login -->
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-sm text-primary-dark/70 hover:text-primary-dark transition-colors duration-300">
                    {{ __('auth.forgot.back_to_login') }}
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>