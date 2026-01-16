<x-app-layout>
    @php $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr'; @endphp
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10" dir="{{ $dir }}">
        <div class="max-w-3xl mx-auto">
            @if(session('status') === 'profile-updated')
            <div class="mb-6 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                {{ __('profile.status') }}
            </div>
            @endif

            <div class="bg-white rounded-2xl shadow p-8 space-y-8">
                <div class="space-y-2">
                    <h1 class="text-2xl font-semibold text-primary-dark">{{ __('profile.title') }}</h1>
                    <p class="text-sm text-neutral-600">{{ __('profile.subtitle') }}</p>
                </div>

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('patch')

                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-full bg-primary-gray flex items-center justify-center overflow-hidden text-primary-dark text-xl font-semibold">
                            @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                            <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm text-neutral-700">{{ __('profile.avatar.label') }}</p>
                            <label class="inline-flex items-center px-4 py-2 bg-primary-dark text-white text-sm font-semibold rounded-lg cursor-pointer hover:bg-primary-dark/90 transition">
                                {{ __('profile.avatar.button') }}
                                <input type="file" name="avatar" accept="image/*" class="hidden">
                            </label>
                            @error('avatar')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="name">{{ __('profile.labels.full_name') }}</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" placeholder="{{ __('profile.placeholders.full_name') }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="email">{{ __('profile.labels.email_address') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" placeholder="{{ __('profile.placeholders.email_address') }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="phone">{{ __('profile.labels.phone') }}</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" placeholder="{{ __('profile.placeholders.phone') }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('phone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="customer_type">{{ __('profile.labels.customer_type') }}</label>
                            <select id="customer_type" name="customer_type" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                                <option value="" @selected(old('customer_type', $user->customer_type) === null)>{{ __('profile.options.select_type') }}</option>
                                <option value="personal" @selected(old('customer_type', $user->customer_type) === 'personal')>{{ __('profile.options.personal') }}</option>
                                <option value="business" @selected(old('customer_type', $user->customer_type) === 'business')>{{ __('profile.options.business') }}</option>
                            </select>
                            @error('customer_type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="address_line1">{{ __('profile.labels.address_line_1') }}</label>
                            <input id="address_line1" name="address_line1" type="text" value="{{ old('address_line1', $user->address_line1 ?? $user->address) }}" placeholder="{{ __('profile.placeholders.address_line_1') }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('address_line1')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="address_line2">{{ __('profile.labels.address_line_2') }}</label>
                            <input id="address_line2" name="address_line2" type="text" value="{{ old('address_line2', $user->address_line2) }}" placeholder="{{ __('profile.placeholders.address_line_2') }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('address_line2')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="postal_code">{{ __('profile.labels.postal_code') }}</label>
                            <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $user->postal_code) }}" placeholder="{{ __('profile.placeholders.postal_code') }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('postal_code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="city">{{ __('profile.labels.city') }}</label>
                            <input id="city" name="city" type="text" value="{{ old('city', $user->city) }}" placeholder="{{ __('profile.placeholders.city') }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('city')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="country">{{ __('profile.labels.country') }}</label>
                            <input id="country" name="country" type="text" value="{{ old('country', $user->country) }}" placeholder="{{ __('profile.placeholders.country') }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('country')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 bg-primary-gray rounded-xl px-4 py-4 border border-neutral-200">
                            <h3 class="text-sm font-semibold text-primary-dark mb-3">{{ __('profile.preferences.title') }}</h3>
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="newsletter_opt_in" name="newsletter_opt_in" value="1" @checked(old('newsletter_opt_in', $user->newsletter_opt_in)) class="h-4 w-4 rounded border-neutral-300 text-primary-dark focus:ring-primary-dark/40">
                                <label for="newsletter_opt_in" class="text-sm text-neutral-700">{{ __('profile.preferences.newsletter') }}</label>
                            </div>
                            @error('newsletter_opt_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition">{{ __('profile.actions.save_changes') }}</button>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-neutral-300 text-sm font-medium rounded-lg text-neutral-700 hover:border-primary-dark hover:text-primary-dark transition">{{ __('profile.actions.cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="max-w-5xl mx-auto mt-8 grid gap-6 md:grid-cols-2" dir="{{ $dir }}">
            <div class="bg-white rounded-2xl shadow p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-primary-dark">{{ __('profile.password.title') }}</h3>
                    <p class="text-sm text-neutral-600">{{ __('profile.password.subtitle') }}</p>
                </div>
                <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    <div>
                        <label for="update_password_current_password" class="block text-sm font-medium text-primary-dark mb-1">{{ __('profile.password.current_label') }}</label>
                        <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition" />
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="update_password_password" class="block text-sm font-medium text-primary-dark mb-1">{{ __('profile.password.new_label') }}</label>
                        <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition" />
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="update_password_password_confirmation" class="block text-sm font-medium text-primary-dark mb-1">{{ __('profile.password.confirm_label') }}</label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition" />
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <button class="inline-flex items-center px-5 py-2.5 bg-primary-dark text-white text-sm font-semibold rounded-lg hover:bg-primary-dark/90 transition">{{ __('profile.actions.save_password') }}</button>
                        @if (session('status') === 'password-updated')
                        <span class="text-sm text-emerald-700">{{ __('profile.password.updated') }}</span>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-primary-dark">{{ __('profile.delete.title') }}</h3>
                    <p class="text-sm text-neutral-600">{{ __('profile.delete.subtitle') }}</p>
                </div>
                <x-danger-button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">{{ __('profile.actions.delete_account') }}</x-danger-button>

                <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                    <form method="post" action="{{ route('profile.destroy') }}" class="p-6 space-y-4">
                        @csrf
                        @method('delete')

                        <h4 class="text-lg font-semibold text-primary-dark">{{ __('profile.delete.confirm_title') }}</h4>
                        <p class="text-sm text-neutral-600">{{ __('profile.delete.confirm_message') }}</p>

                        <div>
                            <label for="password" class="block text-sm font-medium text-primary-dark mb-1">{{ __('profile.delete.password_label') }}</label>
                            <input id="password" name="password" type="password" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition" />
                            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-3">
                            <x-secondary-button x-on:click="$dispatch('close')">{{ __('profile.delete.cancel_button') }}</x-secondary-button>
                            <x-danger-button>{{ __('profile.delete.confirm_button') }}</x-danger-button>
                        </div>
                    </form>
                </x-modal>
            </div>
        </div>
    </div>
</x-app-layout>