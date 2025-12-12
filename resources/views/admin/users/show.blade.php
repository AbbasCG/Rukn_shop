@extends('layouts.admin', ['title' => 'User Details'])

@section('content')
<div class="max-w-5xl mx-auto flex items-center justify-between mb-4">
    <h2 class="text-2xl font-semibold text-primary-800 dark:text-primary-800">{{ $user->name }}</h2>
    <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-primary-800 text-white rounded-lg hover:bg-primary-700 active:bg-primary-900 transition-all">Edit</a>
</div>

<div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-white rounded-xl p-6 shadow border border-neutral-200 dark:border-neutral-300 space-y-4 text-primary-800 dark:text-primary-800">
        <h3 class="text-lg font-semibold mb-4">User Information</h3>
        <div>
            <p class="text-sm text-primary-600 dark:text-primary-600">Name</p>
            <p class="font-medium">{{ $user->name }}</p>
        </div>
        <div>
            <p class="text-sm text-primary-600 dark:text-primary-600">Email</p>
            <p class="font-medium">{{ $user->email }}</p>
        </div>
        <div>
            <p class="text-sm text-primary-600 dark:text-primary-600">Role</p>
            <span class="inline-block px-3 py-1 text-sm rounded-full font-medium {{ $user->role === 'admin' ? 'bg-primary-800 text-white' : 'bg-primary-100 text-primary-800' }}">
                {{ ucfirst($user->role) }}
            </span>
        </div>
        <div>
            <p class="text-sm text-primary-600 dark:text-primary-600">Email Verified</p>
            <span class="inline-block px-3 py-1 text-sm rounded-full font-medium {{ $user->email_verified_at ? 'bg-success-light/20 text-success' : 'bg-neutral-200 text-neutral-700' }}">
                {{ $user->email_verified_at ? 'Verified' : 'Not Verified' }}
            </span>
        </div>
        <div>
            <p class="text-sm text-primary-600 dark:text-primary-600">Joined</p>
            <p class="font-medium">{{ $user->created_at?->format('F d, Y') }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-white rounded-xl p-6 shadow border border-neutral-200 dark:border-neutral-300 text-primary-800 dark:text-primary-800">
        <h3 class="text-lg font-semibold mb-4">Activity</h3>
        <div class="space-y-3">
            <div>
                <p class="text-sm text-primary-600 dark:text-primary-600">Total Orders</p>
                <p class="text-2xl font-bold">{{ $user->orders->count() ?? 0 }}</p>
            </div>
            <div>
                <p class="text-sm text-primary-600 dark:text-primary-600">Total Spent</p>
                <p class="text-2xl font-bold">€{{ number_format($user->orders->sum('total_amount') ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-primary-600 dark:text-primary-600">Last Login</p>
                <p class="font-medium">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
