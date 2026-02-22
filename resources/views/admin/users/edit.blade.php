@extends('layouts.admin', ['title' => 'Edit User'])

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6" x-data="{ resetPassword: {{ old('password') ? 'true' : 'false' }}, showPassword: false, showConfirmPassword: false }">
    <div>
        <h1 class="text-3xl font-bold text-primary-dark">{{ __('Edit User') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('Manage account details, role, and security settings') }}</p>
    </div>

    <form action="{{ route('admin.users.update', ['locale' => app()->getLocale(), 'user' => $user]) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="bg-white rounded-2xl shadow-md p-6 md:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-primary-dark">{{ __('User Overview') }}</h2>
                    <p class="text-sm text-primary-dark/60 mt-1">{{ __('Core profile information and account role') }}</p>
                </div>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border {{ old('role', $user->role) === 'admin' ? 'bg-primary-dark/10 text-primary-dark border-primary-dark/30' : 'bg-neutral-100 text-neutral-700 border-neutral-300' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z"/>
                    </svg>
                    {{ old('role', $user->role) === 'admin' ? __('Administrator') : __('Standard User') }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium text-primary-dark flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-dark/70" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z"/>
                        </svg>
                        {{ __('User Role') }}
                    </label>
                    <div class="relative">
                        <select
                            id="role"
                            name="role"
                            required
                            class="w-full appearance-none px-4 py-3 pr-11 rounded-xl border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                        >
                            <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
                            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-primary-dark/60" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    @error('role')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium text-primary-dark">{{ __('Full Name') }}</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $user->name) }}"
                        required
                        autofocus
                        placeholder="John Doe"
                        class="w-full px-4 py-3 rounded-xl border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                    >
                    @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2 lg:col-span-2">
                    <label for="email" class="text-sm font-medium text-primary-dark">{{ __('Email Address') }}</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        placeholder="user@example.com"
                        class="w-full px-4 py-3 rounded-xl border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50"
                    >
                    @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow-md p-6 md:p-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-primary-dark">{{ __('Security Settings') }}</h2>
                    <p class="text-sm text-primary-dark/60 mt-1">{{ __('Choose whether to reset this user password') }}</p>
                </div>
                <label for="reset-password" class="inline-flex items-center gap-3 cursor-pointer select-none">
                    <span class="text-sm font-medium text-primary-dark">{{ __('Reset Password') }}</span>
                    <button
                        id="reset-password"
                        type="button"
                        role="switch"
                        :aria-checked="resetPassword"
                        @click="resetPassword = !resetPassword; if (!resetPassword) { showPassword = false; showConfirmPassword = false; }"
                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20"
                        :class="resetPassword ? 'bg-primary-dark' : 'bg-neutral-300'"
                    >
                        <span
                            class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform duration-300"
                            :class="resetPassword ? 'translate-x-6' : 'translate-x-1'"
                        ></span>
                    </button>
                </label>
            </div>

            <p class="text-xs text-primary-dark/60">
                {{ __('When disabled, current password remains unchanged. Enable to enter a new password.') }}
            </p>

            <div
                x-show="resetPassword"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="grid grid-cols-1 lg:grid-cols-2 gap-6"
            >
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-primary-dark">{{ __('New Password') }}</label>
                    <div class="relative">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            :disabled="!resetPassword"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 pr-12 rounded-xl border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50 disabled:bg-neutral-100 disabled:cursor-not-allowed"
                        >
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            :aria-label="showPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-primary-dark/60 hover:text-primary-dark focus:outline-none"
                        >
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95M9.88 9.88a3 3 0 104.24 4.24"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.1 6.1A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.962 9.962 0 01-4.293 5.2M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-primary-dark/60">{{ __('Use a strong password with letters, numbers, and symbols.') }}</p>
                    @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-primary-dark">{{ __('Confirm New Password') }}</label>
                    <div class="relative">
                        <input
                            id="password_confirmation"
                            :type="showConfirmPassword ? 'text' : 'password'"
                            name="password_confirmation"
                            :disabled="!resetPassword"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 pr-12 rounded-xl border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50 disabled:bg-neutral-100 disabled:cursor-not-allowed"
                        >
                        <button
                            type="button"
                            @click="showConfirmPassword = !showConfirmPassword"
                            :aria-label="showConfirmPassword ? '{{ __('Hide password confirmation') }}' : '{{ __('Show password confirmation') }}'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-primary-dark/60 hover:text-primary-dark focus:outline-none"
                        >
                            <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.293-3.95M9.88 9.88a3 3 0 104.24 4.24"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.1 6.1A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.962 9.962 0 01-4.293 5.2M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button type="submit" class="sm:flex-1 py-3 px-5 bg-primary-dark text-white font-semibold rounded-xl hover:bg-primary-dark/90 hover:-translate-y-0.5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2">
                {{ __('Update User') }}
            </button>
            <a href="{{ route('admin.users.index', ['locale' => app()->getLocale()]) }}" class="sm:flex-1 py-3 px-5 bg-white border border-primary-gray text-primary-dark font-semibold rounded-xl hover:bg-neutral-50 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 text-center">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
