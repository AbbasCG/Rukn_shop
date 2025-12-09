<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Header Section -->
    <div class="mb-8 text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <x-application-logo class="h-12 w-auto text-primary-dark" />
        </a>
        <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('Welcome back') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('Sign in to your account to continue shopping') }}</p>
    </div>

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-6">
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
                autocomplete="current-password"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="flex items-center cursor-pointer">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    class="w-4 h-4 rounded border-primary-gray bg-white text-primary-dark shadow-sm cursor-pointer focus:ring-2 focus:ring-primary-dark/20" 
                    name="remember">
                <span class="ms-2 text-sm text-primary-dark/70">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-primary-dark/70 hover:text-primary-dark transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-dark" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
            {{ __('Sign In') }}
        </button>

        <!-- Register Link -->
        <div class="text-center">
            <p class="text-sm text-primary-dark/70">
                {{ __('Don\'t have an account?') }}
                <a href="{{ route('register') }}" class="font-semibold text-primary-dark hover:text-primary-dark/80 transition-colors duration-300">
                    {{ __('Create one') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
