@extends('layouts.admin', ['title' => __('admin.users.create.title')])

@section('content')
<div class="w-full max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary-dark">{{ __('admin.users.create.title') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('admin.users.create.subtitle') }}</p>
    </div>

    <!-- Create Form -->
    <form action="{{ r('admin.users.store') }}" method="POST" class="space-y-6 bg-white p-8 rounded-xl shadow-lg">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.users.form.full_name') }}</label>
            <input 
                id="name"
                name="name" 
                type="text"
                value="{{ old('name') }}" 
                required
                autofocus
                placeholder="{{ __('admin.users.form.placeholders.full_name') }}"
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.users.form.email') }}</label>
            <input 
                id="email"
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required
                placeholder="{{ __('admin.users.form.placeholders.email') }}"
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.users.security.password') }}</label>
            <input 
                id="password"
                type="password" 
                name="password" 
                required
                placeholder="{{ __('admin.users.form.placeholders.password') }}"
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            @error('password')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.users.security.confirm_password') }}</label>
            <input 
                id="password_confirmation"
                type="password" 
                name="password_confirmation" 
                required
                placeholder="{{ __('admin.users.form.placeholders.password') }}"
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
        </div>

        <!-- Role -->
        <div>
            <label for="role" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.users.form.role') }}</label>
            <select 
                id="role"
                name="role" 
                required
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                <option value="user" @selected(old('role', 'user') === 'user')>{{ __('admin.users.roles.user') }}</option>
                <option value="admin" @selected(old('role') === 'admin')>{{ __('admin.users.roles.admin') }}</option>
            </select>
            @error('role')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2">
                {{ __('admin.users.actions.create_user') }}
            </button>
            <a href="{{ r('admin.users.index') }}" class="flex-1 py-3 px-4 bg-white border border-primary-gray text-primary-dark font-semibold rounded-lg hover:bg-gray-50 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 text-center">
                {{ __('admin.users.actions.cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
