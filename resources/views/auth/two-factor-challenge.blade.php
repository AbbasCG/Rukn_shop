<x-guest-layout>
    <div>
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div class="mb-8 text-center">
            <a href="{{ r('home') }}" class="inline-block">
                <x-application-logo class="h-12 w-auto text-primary-dark" />
            </a>
            <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('two_factor.challenge.title') }}</h1>
            <p class="mt-2 text-sm text-primary-dark/60">{{ __('two_factor.challenge.intro', ['minutes' => \App\Support\TwoFactor::MINUTES]) }}</p>
            @if ($email)
                <p class="mt-1 text-sm font-medium text-primary-dark" dir="ltr">{{ $email }}</p>
            @endif
        </div>

        <form method="POST" action="{{ r('two-factor.challenge') }}" class="space-y-6">
            @csrf
            <div>
                <x-input-label for="code" :value="__('two_factor.challenge.code_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                <x-text-input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9 ]*" maxlength="7"
                    autocomplete="one-time-code" required autofocus dir="ltr"
                    class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-center text-2xl tracking-[0.5em] text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark" />
                <x-input-error :messages="$errors->get('code')" class="mt-2 text-sm text-red-600" />
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2">
                {{ __('two_factor.challenge.submit') }}
            </button>
        </form>

        <div class="mt-6 flex items-center justify-between gap-4 text-sm">
            @if ($resendWait !== null)
                <form method="POST" action="{{ r('two-factor.resend') }}">
                    @csrf
                    <button type="submit" class="text-primary-dark/70 underline underline-offset-4 hover:text-primary-dark">{{ __('two_factor.challenge.resend') }}</button>
                </form>
            @else
                <span></span>
            @endif
            <a href="{{ r('login') }}" class="text-primary-dark/70 hover:text-primary-dark">{{ __('two_factor.challenge.back') }}</a>
        </div>
        <p class="mt-6 text-xs text-primary-dark/50">{{ __('two_factor.challenge.spam_hint') }}</p>
    </div>
</x-guest-layout>
