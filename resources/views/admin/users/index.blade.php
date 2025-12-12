@extends('layouts.admin', ['title' => 'Users'])

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">
    
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">Users</h1>
            <p class="text-sm text-primary-dark/60 mt-1">Manage users, roles, and account access</p>
        </div>
        <a 
            href="{{ route('admin.users.create') }}"
            class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
            + Create User
        </a>
    </div>

    <!-- Search & Filter Toolbar -->
    <form method="GET" class="bg-white rounded-xl shadow-lg p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search Input -->
            <div>
                <label for="search" class="block text-sm font-medium text-primary-dark mb-2">Search</label>
                <input 
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Name or email..."
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>

            <!-- Role Filter -->
            <div>
                <label for="role" class="block text-sm font-medium text-primary-dark mb-2">Role</label>
                <select 
                    id="role"
                    name="role"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">All Roles</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="user" @selected(request('role') === 'user')>User</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2 lg:col-span-2">
                <button 
                    type="submit"
                    class="flex-1 px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    Search
                </button>
                <a 
                    href="{{ route('admin.users.index') }}"
                    class="flex-1 px-4 py-2 bg-white border border-primary-gray text-primary-dark font-medium rounded-lg hover:bg-primary-dark/5 transition-all duration-300">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- User Statistics Cards -->
    @php
        $totalUsers = \App\Models\User::count();
        $adminUsers = \App\Models\User::where('role', 'admin')->count();
        $normalUsers = \App\Models\User::where('role', 'user')->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Total Users Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">Total Users</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $totalUsers }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">Active accounts</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3.075A6.75 6.75 0 0121 12.75V3m0 0h-6m6 0v6m0-6L9 21"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Admin Users Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">Administrators</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $adminUsers }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">Admin accounts</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Normal Users Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">Regular Users</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $normalUsers }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">Customer accounts</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">User ID</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Name</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Email</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Role</th>
                            <th class="text-left py-4 px-6 text-sm font-semibold text-primary-dark">Created</th>
                            <th class="text-right py-4 px-6 text-sm font-semibold text-primary-dark">Actions</th>
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
                                            Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-neutral-200 text-neutral-700 border border-neutral-300">
                                            User
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm text-primary-dark/70">{{ $user->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <a 
                                            href="{{ route('admin.users.edit', $user) }}"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-dark text-white hover:bg-primary-dark/90 transition-all duration-300">
                                            Edit
                                        </a>
                                        <button 
                                            type="button"
                                            onclick="deleteUser('{{ $user->id }}', '{{ $user->name }}')"
                                            class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition-all duration-300">
                                            Delete
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
                <p class="text-lg font-medium text-primary-dark mb-2">No users found</p>
                <p class="text-sm text-primary-dark/60 text-center mb-6">Try adjusting your search or filter criteria</p>
                <a 
                    href="{{ route('admin.users.create') }}"
                    class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    Create First User
                </a>
            </div>
        @endif
    </div>

</div>

<!-- Delete User Modal/Confirmation -->
<script>
function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete "${userName}"? This action cannot be undone.`)) {
        // Create a hidden form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
