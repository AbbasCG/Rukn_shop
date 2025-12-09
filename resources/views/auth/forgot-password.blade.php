<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Header Section -->
    <div class="mb-8 text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <x-application-logo class="h-12 w-auto text-primary-dark" />
        </a>
        <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('Forgot Password') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('We\'ll help you reset your password and regain access') }}</p>
    </div>

    <!-- Info Message -->
    <div class="mb-8 p-4 rounded-lg bg-primary-light border border-primary-gray">
        <p class="text-sm text-primary-dark/80">
            {{ __('No worries! Enter your email address and we\'ll send you a link to reset your password.') }}
        </p>
    </div>

    <!-- Password Reset Form -->
    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="email" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autofocus 
                placeholder="your@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
            {{ __('Send Reset Link') }}
        </button>

        <!-- Back to Login -->
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm text-primary-dark/70 hover:text-primary-dark transition-colors duration-300">
                {{ __('Back to login') }}
            </a>
        </div>
    </form>
</x-guest-layout>
