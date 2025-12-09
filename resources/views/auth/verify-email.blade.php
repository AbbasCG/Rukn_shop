<x-guest-layout>
    <!-- Header Section -->
    <div class="mb-8 text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <x-application-logo class="h-12 w-auto text-primary-dark" />
        </a>
        <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('Verify Email') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('Please verify your email to complete registration') }}</p>
    </div>

    <!-- Info Message -->
    <div class="mb-8 p-4 rounded-lg bg-blue-50 border border-blue-200">
        <p class="text-sm text-blue-900">
            {{ __('Thanks for signing up! We\'ve sent a verification link to your email address. Please check your inbox and click the link to verify your account.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <p class="text-sm text-green-900 font-medium">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </p>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="space-y-4">
        <!-- Resend Link Button -->
        <form method="POST" action="{{ route('verification.send') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
                {{ __('Resend Verification Link') }}
            </button>
        </form>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full py-3 px-4 bg-primary-gray text-primary-dark font-semibold rounded-lg hover:bg-primary-gray/80 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>

    <!-- Help Text -->
    <div class="mt-6 text-center text-sm text-primary-dark/60">
        <p>{{ __('Didn\'t receive the email?') }} <span class="block mt-1">{{ __('Check your spam folder or contact support.') }}</span></p>
    </div>
</x-guest-layout>
