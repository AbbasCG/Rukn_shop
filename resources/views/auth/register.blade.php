<x-guest-layout>
    @php
        $optionalFields = ['phone', 'address', 'postal_code', 'city', 'country'];
        $hasOptionalErrors = $errors->hasAny($optionalFields);
        $hasOptionalInput = collect($optionalFields)->contains(fn ($field) => filled(old($field)));
        $showOptionalDetails = $hasOptionalErrors || $hasOptionalInput;
    @endphp
    <div>
        <!-- Header Section -->
        <div class="mb-8 text-center">
            <a href="{{ r('home') }}" class="inline-block">
                <x-application-logo class="h-12 w-auto text-primary-dark" />
            </a>
            <h1 class="mt-6 text-3xl font-bold text-primary-dark">{{ __('auth.register.title') }}</h1>
            <p class="mt-2 text-sm text-primary-dark/60">{{ __('auth.register.subtitle') }}</p>
        </div>

        <!-- Registration Form -->
        <form method="POST" action="{{ r('register') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" :value="__('auth.register.name_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                    <x-text-input
                        id="name"
                        class="w-full h-12 px-4 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                        type="text"
                        name="name"
                        :value="old('name')"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="{{ __('auth.register.name_placeholder') }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-red-600" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('auth.register.email_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                    <x-text-input
                        id="email"
                        class="w-full h-12 px-4 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autocomplete="username"
                        placeholder="{{ __('auth.register.email_placeholder') }}" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('auth.register.password_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                    <x-text-input
                        id="password"
                        class="w-full h-12 px-4 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="{{ __('auth.register.password_placeholder') }}" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
                    <p class="mt-1 text-xs text-primary-dark/50">{{ __('auth.register.password_hint') }}</p>
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('auth.register.confirm_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                    <x-text-input
                        id="password_confirmation"
                        class="w-full h-12 px-4 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="{{ __('auth.register.password_placeholder') }}" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-600" />
                </div>
            </div>

            <div x-data="{ showExtra: @js($showOptionalDetails) }" class="rounded-xl border border-primary-gray/80 bg-white/70 overflow-hidden">
                <button
                    type="button"
                    @click="showExtra = !showExtra"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-primary-dark hover:bg-primary-light/30 transition"
                    :aria-expanded="showExtra.toString()">
                    <span>{{ __('auth.register.add_more_details') }}</span>
                    <svg class="w-4 h-4 transition-transform duration-300" :class="showExtra ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="showExtra"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="px-4 pb-4 pt-3 border-t border-primary-gray/70">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="phone" :value="__('auth.register.phone_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                            <x-text-input
                                id="phone"
                                class="w-full h-12 px-4 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                                type="tel"
                                name="phone"
                                :value="old('phone')"
                                autocomplete="tel"
                                placeholder="{{ __('auth.register.phone_placeholder') }}" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2 text-sm text-red-600" />
                        </div>

                        <div>
                            <x-input-label for="city" :value="__('auth.register.city_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                            <x-text-input
                                id="city"
                                class="w-full h-12 px-4 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                                type="text"
                                name="city"
                                :value="old('city')"
                                autocomplete="address-level2"
                                placeholder="{{ __('auth.register.city_placeholder') }}" />
                            <x-input-error :messages="$errors->get('city')" class="mt-2 text-sm text-red-600" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="address" :value="__('auth.register.address_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                            <x-text-input
                                id="address"
                                class="w-full h-12 px-4 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                                type="text"
                                name="address"
                                :value="old('address')"
                                autocomplete="street-address"
                                placeholder="{{ __('auth.register.address_placeholder') }}" />
                            <x-input-error :messages="$errors->get('address')" class="mt-2 text-sm text-red-600" />
                        </div>

                        <div>
                            <x-input-label for="postal_code" :value="__('auth.register.postal_code_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                            <x-text-input
                                id="postal_code"
                                class="w-full h-12 px-4 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                                type="text"
                                name="postal_code"
                                :value="old('postal_code')"
                                autocomplete="postal-code"
                                placeholder="{{ __('auth.register.postal_code_placeholder') }}" />
                            <x-input-error :messages="$errors->get('postal_code')" class="mt-2 text-sm text-red-600" />
                        </div>

                        <div>
                            <x-input-label for="country" :value="__('auth.register.country_label')" class="block text-sm font-medium text-primary-dark mb-2" />
                            <x-text-input
                                id="country"
                                class="w-full h-12 px-4 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                                type="text"
                                name="country"
                                :value="old('country')"
                                autocomplete="country-name"
                                placeholder="{{ __('auth.register.country_placeholder') }}" />
                            <x-input-error :messages="$errors->get('country')" class="mt-2 text-sm text-red-600" />
                        </div>
                    </div>
                </div>
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
                    <a href="{{ r('terms-of-service') }}" target="_blank" rel="noopener" class="text-primary-dark font-semibold underline hover:text-primary-dark/80 transition-colors duration-300">{{ __('auth.register.terms_of_service') }}</a>
                    {{ __('auth.register.and') }}
                    <a href="{{ r('privacy-policy') }}" target="_blank" rel="noopener" class="text-primary-dark font-semibold underline hover:text-primary-dark/80 transition-colors duration-300">{{ __('auth.register.privacy_policy') }}</a>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full h-12 px-4 bg-primary-dark text-white font-semibold rounded-xl shadow-md hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 focus:ring-offset-primary-light">
                {{ __('auth.register.submit') }}
            </button>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-sm text-primary-dark/70">
                    {{ __('auth.register.have_account') }}
                    <a href="{{ r('login') }}" class="font-semibold text-primary-dark hover:text-primary-dark/80 transition-colors duration-300">
                        {{ __('auth.register.sign_in') }}
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>
