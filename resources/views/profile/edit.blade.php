<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="max-w-3xl mx-auto">
            @if(session('status') === 'profile-updated')
                <div class="mb-6 px-4 py-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">
                    Your profile has been updated.
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow p-8 space-y-8">
                <div class="space-y-2">
                    <h1 class="text-2xl font-semibold text-primary-dark">My Profile</h1>
                    <p class="text-sm text-neutral-600">Manage your personal information and account settings.</p>
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
                            <p class="text-sm text-neutral-700">Upload a square image (JPG, PNG, WEBP). Max 5 MB.</p>
                            <label class="inline-flex items-center px-4 py-2 bg-primary-dark text-white text-sm font-semibold rounded-lg cursor-pointer hover:bg-primary-dark/90 transition">
                                Change photo
                                <input type="file" name="avatar" accept="image/*" class="hidden">
                            </label>
                            @error('avatar')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="name">Full name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="email">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="phone">Phone</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('phone')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="customer_type">Customer type</label>
                            <select id="customer_type" name="customer_type" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                                <option value="" @selected(old('customer_type', $user->customer_type) === null)>Select type</option>
                                <option value="personal" @selected(old('customer_type', $user->customer_type) === 'personal')>Personal</option>
                                <option value="business" @selected(old('customer_type', $user->customer_type) === 'business')>Business</option>
                            </select>
                            @error('customer_type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="address_line1">Address line 1</label>
                            <input id="address_line1" name="address_line1" type="text" value="{{ old('address_line1', $user->address_line1 ?? $user->address) }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('address_line1')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="address_line2">Address line 2</label>
                            <input id="address_line2" name="address_line2" type="text" value="{{ old('address_line2', $user->address_line2) }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('address_line2')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="postal_code">Postal code</label>
                            <input id="postal_code" name="postal_code" type="text" value="{{ old('postal_code', $user->postal_code) }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('postal_code')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="city">City</label>
                            <input id="city" name="city" type="text" value="{{ old('city', $user->city) }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('city')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-primary-dark mb-1" for="country">Country</label>
                            <input id="country" name="country" type="text" value="{{ old('country', $user->country) }}" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition">
                            @error('country')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2 bg-primary-gray rounded-xl px-4 py-4 border border-neutral-200">
                            <h3 class="text-sm font-semibold text-primary-dark mb-3">Account preferences</h3>
                            <div class="flex items-center gap-3">
                                <input type="checkbox" id="newsletter_opt_in" name="newsletter_opt_in" value="1" @checked(old('newsletter_opt_in', $user->newsletter_opt_in)) class="h-4 w-4 rounded border-neutral-300 text-primary-dark focus:ring-primary-dark/40">
                                <label for="newsletter_opt_in" class="text-sm text-neutral-700">I want to receive news and offers</label>
                            </div>
                            @error('newsletter_opt_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition">Save changes</button>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-neutral-300 text-sm font-medium rounded-lg text-neutral-700 hover:border-primary-dark hover:text-primary-dark transition">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="max-w-5xl mx-auto mt-8 grid gap-6 md:grid-cols-2">
            <div class="bg-white rounded-2xl shadow p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-semibold text-primary-dark">Update Password</h3>
                    <p class="text-sm text-neutral-600">Keep your account secure with a strong password.</p>
                </div>
                <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    <div>
                        <label for="update_password_current_password" class="block text-sm font-medium text-primary-dark mb-1">Current password</label>
                        <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition" />
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="update_password_password" class="block text-sm font-medium text-primary-dark mb-1">New password</label>
                        <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition" />
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <label for="update_password_password_confirmation" class="block text-sm font-medium text-primary-dark mb-1">Confirm password</label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition" />
                        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <button class="inline-flex items-center px-5 py-2.5 bg-primary-dark text-white text-sm font-semibold rounded-lg hover:bg-primary-dark/90 transition">Save password</button>
                        @if (session('status') === 'password-updated')
                            <span class="text-sm text-emerald-700">Saved.</span>
                        @endif
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow p-6 space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-primary-dark">Delete Account</h3>
                    <p class="text-sm text-neutral-600">This action is permanent. Please confirm with your password.</p>
                </div>
                <x-danger-button
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                >Delete Account</x-danger-button>

                <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                    <form method="post" action="{{ route('profile.destroy') }}" class="p-6 space-y-4">
                        @csrf
                        @method('delete')

                        <h4 class="text-lg font-semibold text-primary-dark">Are you sure?</h4>
                        <p class="text-sm text-neutral-600">Once deleted, your data cannot be recovered.</p>

                        <div>
                            <label for="password" class="block text-sm font-medium text-primary-dark mb-1">Password</label>
                            <input id="password" name="password" type="password" class="w-full rounded-lg border border-neutral-300 px-4 py-2.5 text-sm focus:ring-primary-dark/20 focus:border-primary-dark transition" placeholder="Password" />
                            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-3">
                            <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                            <x-danger-button>Delete Account</x-danger-button>
                        </div>
                    </form>
                </x-modal>
            </div>
        </div>
    </div>
</x-app-layout>
