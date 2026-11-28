<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full md:translate-x-0 md:rtl:translate-x-0'" class="fixed md:static inset-y-0 start-0 border-e w-64 bg-white dark:bg-white border-primary-gray dark:border-primary-gray transform transition-transform duration-300 ease-in-out z-40">
    
    <!-- Sidebar Header -->
    <div class="h-24 px-6 flex items-center justify-between border-b border-primary-gray dark:border-primary-gray">
        <a href="{{ r('admin.dashboard') }}" class="flex items-center gap-2">
            <x-application-logo class="block h-12 w-auto fill-current text-primary-dark" />
        </a>
        <button class="md:hidden p-2 rounded-lg hover:bg-primary-gray transition-all duration-200" @click="sidebarOpen=false" title="{{ __('admin.sidebar.tooltips.close_sidebar') }}">
            <svg class="w-6 h-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-1 flex-1">
        <!-- Navigation label -->
        <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-primary-dark/50 dark:text-primary-dark/50 mb-3">
            {{ __('admin.sidebar.sections.menu') }}
        </div>

        @php
            $links = [
                ['label' => __('admin.sidebar.items.dashboard'), 'route' => 'admin.dashboard', 'icon' => 'home'],
                ['label' => __('admin.sidebar.items.products'), 'route' => 'admin.products.index', 'icon' => 'archive-box'],
                ['label' => __('admin.sidebar.items.categories'), 'route' => 'admin.categories.index', 'icon' => 'tag'],
                ['label' => __('size_guide.admin.title'), 'route' => 'admin.size-guide.edit', 'icon' => 'ruler'],
                ['label' => __('admin.sidebar.items.orders'), 'route' => 'admin.orders.index', 'icon' => 'shopping-bag'],
                ['label' => __('admin.sidebar.items.reports'), 'route' => 'admin.reports.index', 'icon' => 'chart-bar'],
                ['label' => __('feeds.admin.title'), 'route' => 'admin.feeds.index', 'icon' => 'tag'],
                ['label' => __('admin.sidebar.items.messages'), 'route' => 'admin.messages.index', 'icon' => 'envelope', 'badge' => \App\Models\ContactMessage::unread()->count()],
                ['label' => __('coupons.admin.title'), 'route' => 'admin.coupons.index', 'icon' => 'tag'],
                ['label' => __('reviews.admin.title'), 'route' => 'admin.reviews.index', 'icon' => 'star', 'badge' => \App\Models\Review::pending()->count()],
                ['label' => __('admin.sidebar.items.users'), 'route' => 'admin.users.index', 'icon' => 'users'],
            ];
        @endphp
        @foreach($links as $item)
            @php
                $isActive = request()->routeIs(str_replace('index','*',$item['route']));
            @endphp
            <x-admin.nav-link :href="r($item['route'])" :active="$isActive">
                <x-slot name="icon">
                    @include('admin.partials.icon', ['name' => $item['icon']])
                </x-slot>
                {{ $item['label'] }}
                @if(!empty($item['badge']))
                    <span class="ms-2 inline-flex min-w-5 h-5 px-1.5 items-center justify-center rounded-full bg-amber-500 text-white text-xs font-bold">{{ $item['badge'] }}</span>
                @endif
            </x-admin.nav-link>
        @endforeach

        <!-- Soon Section -->
        <div class="px-3 py-4 mt-6">
            <div class="text-xs font-semibold uppercase tracking-wider text-primary-dark/50 dark:text-primary-dark/50 mb-3">
                {{ __('admin.sidebar.sections.coming_soon') }}
            </div>
            @php
                $soonLinks = [
                    ['label' => __('admin.sidebar.items.settings'), 'icon' => 'cog'],
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
                    <img src="{{ $sidebarUser->avatarUrl() }}" alt="{{ $sidebarUser->name }}" class="h-full w-full object-cover">
                @else
                    <span>{{ $sidebarInitial }}</span>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary-dark/50 dark:text-primary-dark/50 mb-0.5">{{ __('admin.footer.logged_in_as') }}</p>
                <p class="text-sm font-medium text-primary-dark dark:text-primary-dark truncate">{{ $sidebarUser->name }}</p>
            </div>
        </div>

        <!-- Quick Links -->
        <a 
            href="{{ r('home') }}" 
            class="flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-primary-gray dark:bg-primary-gray text-primary-dark hover:bg-primary-gray/80 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            {{ __('admin.footer.back_to_shop') }}
        </a>
    </div>

</aside>
