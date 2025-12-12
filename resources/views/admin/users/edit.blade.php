@extends('layouts.admin', ['title' => 'Edit User'])

@section('content')
<div class="w-full max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-primary-dark">{{ __('Edit User') }}</h1>
        <p class="mt-2 text-sm text-primary-dark/60">{{ __('Update user information') }}</p>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6 bg-white p-8 rounded-xl shadow-lg">
        @csrf
        @method('PUT')

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-primary-dark mb-2">{{ __('Full Name') }}</label>
            <input 
                id="name"
                name="name" 
                type="text"
                value="{{ old('name', $user->name) }}" 
                required
                autofocus
                placeholder="John Doe"
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-primary-dark mb-2">{{ __('Email Address') }}</label>
            <input 
                id="email"
                type="email" 
                name="email" 
                value="{{ old('email', $user->email) }}" 
                required
                placeholder="user@example.com"
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-primary-dark mb-2">{{ __('Password') }}</label>
            <input 
                id="password"
                type="password" 
                name="password" 
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            <p class="mt-1 text-xs text-primary-dark/60">{{ __('Leave blank to keep current password') }}</p>
            @error('password')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-primary-dark mb-2">{{ __('Confirm Password') }}</label>
            <input 
                id="password_confirmation"
                type="password" 
                name="password_confirmation" 
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
        </div>

        <!-- Role -->
        <div>
            <label for="role" class="block text-sm font-medium text-primary-dark mb-2">{{ __('User Role') }}</label>
            <select 
                id="role"
                name="role" 
                required
                class="w-full px-4 py-3 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
            </select>
            @error('role')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 py-3 px-4 bg-primary-dark text-white font-semibold rounded-lg hover:bg-primary-dark/90 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2">
                {{ __('Update User') }}
            </button>
            <a href="{{ route('admin.users.index') }}" class="flex-1 py-3 px-4 bg-white border border-primary-gray text-primary-dark font-semibold rounded-lg hover:bg-gray-50 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:ring-offset-2 text-center">
                {{ __('Cancel') }}
            </a>
        </div>
    </form>
</div>
@endsection
