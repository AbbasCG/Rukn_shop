<x-guest-layout>
    @php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
    <div dir="{{ $dir }}">
    <!-- Header Section -->
    <div class="mb-8 text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <x-application-logo class="h-12 w-auto text-primary-dark" />
        </a>
        <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('auth.register.title') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('auth.register.subtitle') }}</p>
    </div>

    <!-- Registration Form -->
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('auth.register.name_label')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="name" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50" 
                type="text" 
                name="name" 
                :value="old('name')" 
                required 
                autofocus 
                autocomplete="name"
                placeholder="{{ __('auth.register.name_placeholder') }}" />
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-red-600" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.register.email_label')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="email" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autocomplete="username"
                placeholder="{{ __('auth.register.email_placeholder') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('auth.register.password_label')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="password" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                type="password"
                name="password"
                required 
                autocomplete="new-password"
                placeholder="{{ __('auth.register.password_placeholder') }}" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
            <p class="mt-1 text-xs text-primary-dark/50">{{ __('auth.register.password_hint') }}</p>
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('auth.register.confirm_label')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="password_confirmation" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password"
                placeholder="{{ __('auth.register.password_placeholder') }}" />
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
                {{ __('auth.register.terms_prefix') }}
                <a href="#" class="text-primary-dark font-semibold hover:text-primary-dark/80 transition-colors duration-300">{{ __('auth.register.terms_of_service') }}</a>
                {{ __('auth.register.and') }}
                <a href="#" class="text-primary-dark font-semibold hover:text-primary-dark/80 transition-colors duration-300">{{ __('auth.register.privacy_policy') }}</a>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
            {{ __('auth.register.submit') }}
        </button>

        <!-- Login Link -->
        <div class="text-center">
            <p class="text-sm text-primary-dark/70">
                {{ __('auth.register.have_account') }}
                <a href="{{ route('login') }}" class="font-semibold text-primary-dark hover:text-primary-dark/80 transition-colors duration-300">
                    {{ __('auth.register.sign_in') }}
                </a>
            </p>
        </div>
    </form>
    </div>
</x-guest-layout>
