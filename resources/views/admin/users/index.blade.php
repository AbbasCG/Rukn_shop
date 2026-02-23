@extends('layouts.admin', ['title' => __('admin.users.index.title')])

@section('content')
@php
    $userDeleteActionTemplate = route('admin.users.destroy', ['locale' => app()->getLocale(), 'user' => '__USER__']);
@endphp

<div x-data="{ 
    deleteModalOpen: false, 
    deleteUserId: null, 
    deleteUserName: '' 
}" @keydown.escape="deleteModalOpen = false" class="w-full max-w-7xl mx-auto space-y-6">
    
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">{{ __('admin.users.index.title') }}</h1>
            <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.users.index.subtitle') }}</p>
        </div>
        <a 
            href="{{ r('admin.users.create') }}"
            class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
            + {{ __('admin.users.actions.create_user') }}
        </a>
    </div>

    <!-- Search & Filter Toolbar -->
    <form method="GET" class="bg-white rounded-xl shadow-lg p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search Input -->
            <div>
                <label for="q" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.users.index.filters.search') }}</label>
                <input 
                    type="text"
                    id="q"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="{{ __('admin.users.index.filters.search_placeholder') }}"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>

            <!-- Role Filter -->
            <div>
                <label for="role" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.users.index.filters.role') }}</label>
                <select 
                    id="role"
                    name="role"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">{{ __('admin.users.index.filters.all_roles') }}</option>
                    <option value="admin" @selected(request('role') === 'admin')>{{ __('admin.users.roles.admin') }}</option>
                    <option value="customer" @selected(request('role') === 'customer')>{{ __('admin.users.roles.customer') }}</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
            <button 
                type="submit"
                class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                {{ __('admin.users.actions.search') }}
            </button>
            <a 
                href="{{ r('admin.users.index') }}"
                class="px-4 py-2 bg-white border border-primary-gray text-primary-dark font-medium rounded-lg hover:bg-primary-dark/5 transition-all duration-300">
                {{ __('admin.users.actions.reset') }}
            </a>
        </div>
    </form>

    <!-- User Statistics Cards -->
    @php
        $totalUsers = \App\Models\User::count();
        $adminUsers = \App\Models\User::where('role', 'admin')->count();
        $normalUsers = \App\Models\User::where('role', 'customer')->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Total Users Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.users.stats.total') }}</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $totalUsers }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.users.stats.active_accounts') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-7 h-7 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 20a3 3 0 00-3-3H9a3 3 0 00-3 3m12 0h3v-1a3 3 0 00-2.2-2.9M18 20H6m-3 0v-1a3 3 0 012.2-2.9M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 2a2 2 0 11-4 0 2 2 0 014 0zM7 9a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Admin Users Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.users.stats.admin') }}</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $adminUsers }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.users.stats.admin_accounts') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-7 h-7 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Normal Users Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.users.stats.regular') }}</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $normalUsers }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.users.stats.customer_accounts') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-7 h-7 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM18 20a6 6 0 00-12 0v2h12v-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Table -->
        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-primary-gray bg-primary-dark/5">
                            <th class="text-start py-4 px-6 text-sm font-semibold text-primary-dark">{{ __('admin.users.table.user_id') }}</th>
                            <th class="text-start py-4 px-6 text-sm font-semibold text-primary-dark">{{ __('admin.users.table.name') }}</th>
                            <th class="text-start py-4 px-6 text-sm font-semibold text-primary-dark">{{ __('admin.users.table.email') }}</th>
                            <th class="text-start py-4 px-6 text-sm font-semibold text-primary-dark">{{ __('admin.users.table.role') }}</th>
                            <th class="text-start py-4 px-6 text-sm font-semibold text-primary-dark">{{ __('admin.users.table.created_at') }}</th>
                            <th class="text-end py-4 px-6 text-sm font-semibold text-primary-dark">{{ __('admin.users.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="border-b border-primary-gray/30 hover:bg-primary-dark/5 transition-colors duration-200">
                                <td class="py-4 px-6">
                                    <span class="text-sm font-semibold text-primary-dark">#{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm font-medium text-primary-dark">{{ $user->name }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm text-primary-dark/70">{{ $user->email }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    @if($user->role === 'admin')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary-dark text-white border border-primary-dark/30">
                                            {{ __('admin.users.roles.admin') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-neutral-200 text-neutral-700 border border-neutral-300">
                                            {{ __('admin.users.roles.user') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm text-primary-dark/70">{{ $user->created_at->translatedFormat('M d, Y') }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <a 
                                            href="{{ route('admin.users.edit', ['locale' => app()->getLocale(), 'user' => $user]) }}"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-dark text-white hover:bg-primary-dark/90 transition-all duration-300">
                                            {{ __('admin.users.actions.edit') }}
                                        </a>
                                        <button 
                                            type="button"
                                            @click="deleteModalOpen = true; deleteUserId = '{{ $user->id }}'; deleteUserName = @js($user->name);"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition-all duration-300">
                                            {{ __('admin.users.actions.delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-primary-dark/5 px-6 py-4 border-t border-primary-gray">
                {{ $users->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-16 px-6">
                <svg class="w-16 h-16 text-primary-dark/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM18 20a6 6 0 00-12 0v2h12v-2z"/>
                </svg>
                <p class="text-lg font-medium text-primary-dark mb-2">{{ __('admin.users.empty.title') }}</p>
                <p class="text-sm text-primary-dark/60 text-center mb-6">{{ __('admin.users.empty.subtitle') }}</p>
                <a 
                    href="{{ r('admin.users.create') }}"
                    class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    {{ __('admin.users.actions.create_first') }}
                </a>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div 
        x-show="deleteModalOpen" 
        @click.self="deleteModalOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div 
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-6"
        >
            <div>
                <h3 class="text-2xl font-bold text-primary-dark">{{ __('admin.users.delete_modal.title') }}</h3>
            </div>

            <div class="space-y-2">
                <p class="text-sm text-neutral-600">
                    {{ __('admin.users.delete_modal.message') }} <span class="font-semibold text-primary-dark" x-text="deleteUserName"></span>? {{ __('admin.users.delete_modal.cannot_undo') }}
                </p>
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button 
                    type="button"
                    @click="deleteModalOpen = false"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-neutral-300 text-neutral-700 font-semibold text-sm hover:bg-neutral-50 transition-all duration-200">
                    {{ __('admin.users.actions.cancel') }}
                </button>
                <form 
                    :action="'{{ $userDeleteActionTemplate }}'.replace('__USER__', deleteUserId)"
                    method="POST" 
                    style="display: none;"
                    @keydown.enter.window="if (deleteModalOpen) this.submit()"
                    id="deleteUserForm"
                >
                    @csrf
                    @method('DELETE')
                </form>
                <button 
                    type="button"
                    @click="document.getElementById('deleteUserForm').submit(); deleteModalOpen = false;"
                    class="flex-1 px-4 py-2.5 rounded-lg bg-red-600 text-white font-semibold text-sm hover:bg-red-700 transition-all duration-200 active:scale-95">
                    {{ __('admin.users.actions.delete') }}
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
