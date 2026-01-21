<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="fixed md:static inset-y-0 left-0 w-64 bg-white dark:bg-white border-r border-primary-gray dark:border-primary-gray transform transition-transform duration-300 ease-in-out z-40" style="font-family: 'Signika', sans-serif;">
    
    <!-- Sidebar Header -->
    <div class="h-24 px-6 flex items-center justify-between border-b border-primary-gray dark:border-primary-gray">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
            <x-application-logo class="block h-12 w-auto fill-current text-primary-dark" />
        </a>
        <button class="md:hidden p-2 rounded-lg hover:bg-primary-gray transition-all duration-200" @click="sidebarOpen=false" title="Close sidebar">
            <svg class="w-6 h-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-1 flex-1">
        <!-- Navigation label -->
        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-primary-dark/50 dark:text-primary-dark/50 mb-3">
            Menu
        </div>

        @php
            $links = [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home'],
                ['label' => 'Products', 'route' => 'admin.products.index', 'icon' => 'archive-box'],
                ['label' => 'Categories', 'route' => 'admin.categories.index', 'icon' => 'tag'],
                ['label' => 'Orders', 'route' => 'admin.orders.index', 'icon' => 'shopping-bag'],
                ['label' => 'Users', 'route' => 'admin.users.index', 'icon' => 'users'],
            ];
        @endphp
        @foreach($links as $item)
            @php
                $isActive = request()->routeIs(str_replace('index','*',$item['route']));
            @endphp
            <x-admin.nav-link :href="route($item['route'])" :active="$isActive">
                <x-slot name="icon">
                    @include('admin.partials.icon', ['name' => $item['icon']])
                </x-slot>
                {{ $item['label'] }}
            </x-admin.nav-link>
        @endforeach

        <!-- Soon Section -->
        <div class="px-3 py-4 mt-6">
            <div class="text-xs font-semibold uppercase tracking-wider text-primary-dark/50 dark:text-primary-dark/50 mb-3">
                Coming Soon
            </div>
            @php
                $soonLinks = [
                    ['label' => 'Settings', 'icon' => 'cog'],
                    ['label' => 'Reports', 'icon' => 'chart-bar'],
                ];
            @endphp
            @foreach($soonLinks as $item)
                <x-admin.nav-link :disabled="true">
                    <x-slot name="icon">
                        @include('admin.partials.icon', ['name' => $item['icon']])
                    </x-slot>
                    {{ $item['label'] }}
                </x-admin.nav-link>
            @endforeach
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-gray dark:border-primary-gray space-y-3">
        <!-- User Info -->
        <div class="px-3 py-2 flex items-center gap-3">
            @php
                $sidebarUser = auth()->user();
                $sidebarInitial = strtoupper(substr($sidebarUser->name ?? 'U', 0, 1));
            @endphp
            <div class="h-10 w-10 rounded-full overflow-hidden border border-neutral-200 flex items-center justify-center bg-primary-dark text-white text-sm font-semibold">
                @if($sidebarUser->avatar ?? false)
                    <img src="{{ asset('storage/' . $sidebarUser->avatar) }}" alt="{{ $sidebarUser->name }}" class="h-full w-full object-cover">
                @else
                    <span>{{ $sidebarInitial }}</span>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary-dark/50 dark:text-primary-dark/50 mb-0.5">Logged in as</p>
                <p class="text-sm font-medium text-primary-dark dark:text-primary-dark truncate">{{ $sidebarUser->name }}</p>
            </div>
        </div>

        <!-- Quick Links -->
        <a 
            href="{{ route('products.index') }}" 
            class="flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-primary-gray dark:bg-primary-gray text-primary-dark hover:bg-primary-gray/80 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Back to Shop
        </a>
    </div>

</aside>
