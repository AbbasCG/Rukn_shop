<x-guest-layout>
    <!-- Header Section -->
    <div class="mb-8 text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <x-application-logo class="h-12 w-auto text-primary-dark" />
        </a>
        <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('Create Account') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('Join us and start shopping with exclusive deals') }}</p>
    </div>

    <!-- Registration Form -->
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Full Name')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="name" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50" 
                type="text" 
                name="name" 
                :value="old('name')" 
                required 
                autofocus 
                autocomplete="name"
                placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-red-600" />
        </div>

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
                autocomplete="username"
                placeholder="your@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="password" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                type="password"
                name="password"
                required 
                autocomplete="new-password"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
            <p class="mt-1 text-xs text-primary-dark/50">{{ __('At least 8 characters with mixed case and numbers') }}</p>
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="password_confirmation" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-600" />
        </div>

        <!-- Terms Agreement -->
        <div class="flex items-start">
            <input 
                type="checkbox" 
                id="terms" 
                name="terms"
                class="w-4 h-4 rounded border-primary-gray bg-white text-primary-dark shadow-sm cursor-pointer focus:ring-2 focus:ring-primary-dark/20 mt-1" />
            <label for="terms" class="ms-2 text-sm text-primary-dark/70 cursor-pointer">
                {{ __('I agree to the') }}
                <a href="#" class="text-primary-dark font-semibold hover:text-primary-dark/80 transition-colors duration-300">{{ __('Terms of Service') }}</a>
                {{ __('and') }}
                <a href="#" class="text-primary-dark font-semibold hover:text-primary-dark/80 transition-colors duration-300">{{ __('Privacy Policy') }}</a>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
            {{ __('Create Account') }}
        </button>

        <!-- Login Link -->
        <div class="text-center">
            <p class="text-sm text-primary-dark/70">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="font-semibold text-primary-dark hover:text-primary-dark/80 transition-colors duration-300">
                    {{ __('Sign in') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
