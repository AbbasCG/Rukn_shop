<x-guest-layout>
    @php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
    <div dir="{{ $dir }}">
    <!-- Header Section -->
    <div class="mb-8 text-center">
        <a href="{{ route('home') }}" class="inline-block">
            <x-application-logo class="h-12 w-auto text-primary-dark" />
        </a>
        <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('auth.reset.title') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('auth.reset.subtitle') }}</p>
    </div>

    <!-- Reset Password Form -->
    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.reset.email_label')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="email" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50" 
                type="email" 
                name="email" 
                :value="old('email', $request->email)" 
                required 
                autofocus 
                autocomplete="username"
                placeholder="{{ __('auth.reset.email_placeholder') }}" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('auth.reset.password_label')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="password" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50" 
                type="password" 
                name="password" 
                required 
                autocomplete="new-password"
                placeholder="{{ __('auth.reset.password_placeholder') }}" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('auth.reset.confirm_label')" class="block text-sm font-medium text-primary-dark mb-2" />
            <x-text-input 
                id="password_confirmation" 
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password"
                placeholder="{{ __('auth.reset.password_placeholder') }}" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-600" />
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
            {{ __('auth.reset.submit') }}
        </button>
    </form>
    </div>
</x-guest-layout>
