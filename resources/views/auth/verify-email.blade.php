<x-guest-layout>
    @php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
    <div dir="{{ $dir }}">
    <!-- Header Section -->
    <div class="mb-8 text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <x-application-logo class="h-12 w-auto text-primary-dark" />
        </a>
        <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('auth.verify.title') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('auth.verify.subtitle') }}</p>
    </div>

    <!-- Info Message -->
    <div class="mb-8 p-4 rounded-lg bg-blue-50 border border-blue-200">
        <p class="text-sm text-blue-900">
            {{ __('auth.verify.message') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <p class="text-sm text-green-900 font-medium">
                {{ __('auth.verify.resent') }}
            </p>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="space-y-4">
        <!-- Resend Link Button -->
        <form method="POST" action="{{ route('verification.send') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
                {{ __('auth.verify.resend') }}
            </button>
        </form>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full py-3 px-4 bg-primary-gray text-primary-dark font-semibold rounded-lg hover:bg-primary-gray/80 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
                {{ __('auth.verify.logout') }}
            </button>
        </form>
    </div>

    <!-- Help Text -->
    <div class="mt-6 text-center text-sm text-primary-dark/60">
        <p>{{ __('auth.verify.not_received') }} <span class="block mt-1">{{ __('auth.verify.help') }}</span></p>
    </div>
    </div>
</x-guest-layout>
