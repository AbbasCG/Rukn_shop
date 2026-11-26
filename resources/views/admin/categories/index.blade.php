@extends('layouts.admin', ['title' => __('admin.categories.index.title')])

@section('content')
@php
    $categoryDeleteActionTemplate = route('admin.categories.destroy', ['locale' => app()->getLocale(), 'category' => '__CATEGORY__']);
@endphp

<div x-data="{ 
    deleteModalOpen: false, 
    deleteCategoryId: null, 
    deleteCategoryName: '', 
    deleteForm: null 
}" @keydown.escape="deleteModalOpen = false" class="w-full max-w-7xl mx-auto space-y-6">
    
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">{{ __('admin.categories.index.title') }}</h1>
            <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.categories.index.subtitle') }}</p>
        </div>
        <a 
            href="{{ r('admin.categories.create') }}"
            class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-dark text-white text-sm font-semibold hover:bg-primary-dark/90 transition-all duration-300">
            + {{ __('admin.categories.actions.add_new') }}
        </a>
    </div>

    <!-- Search & Filter Toolbar -->
    <form method="GET" class="bg-white rounded-xl shadow-lg p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search Input -->
            <div>
                <label for="q" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.categories.index.filters.search') }}</label>
                <input 
                    type="text"
                    id="q"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="{{ __('admin.categories.index.filters.search_placeholder') }}"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.categories.index.filters.status') }}</label>
                <select 
                    id="status"
                    name="status"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">{{ __('admin.categories.index.filters.all_categories') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('admin.categories.status.active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('admin.categories.status.inactive') }}</option>
                </select>
            </div>

            <!-- Sort Filter -->
            <div>
                <label for="sort" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.categories.index.filters.sort_by') }}</label>
                <select 
                    id="sort"
                    name="sort"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="newest" @selected(request('sort') === 'newest')>{{ __('admin.categories.index.filters.sort_newest') }}</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>{{ __('admin.categories.index.filters.sort_oldest') }}</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>{{ __('admin.categories.index.filters.sort_name_asc') }}</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>{{ __('admin.categories.index.filters.sort_name_desc') }}</option>
                    <option value="products" @selected(request('sort') === 'products')>{{ __('admin.categories.index.filters.sort_most_products') }}</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
            <button 
                type="submit"
                class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                {{ __('admin.categories.actions.search') }}
            </button>
            <a 
                href="{{ r('admin.categories.index') }}"
                class="px-4 py-2 bg-white border border-primary-gray text-primary-dark font-medium rounded-lg hover:bg-primary-dark/5 transition-all duration-300">
                {{ __('admin.categories.actions.reset') }}
            </a>
        </div>
    </form>

    <!-- Category Statistics Cards -->
    @php
        $totalCategories = \App\Models\Category::count();
        $activeCategories = \App\Models\Category::where('is_active', true)->count();
        $emptyCategories = \App\Models\Category::doesntHave('products')->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Total Categories Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.categories.index.stats.total_categories') }}</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $totalCategories }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.categories.index.stats.in_catalog') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Categories Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.categories.index.stats.active_categories') }}</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $activeCategories }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.categories.index.stats.published') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Empty Categories Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">{{ __('admin.categories.index.stats.no_products') }}</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $emptyCategories }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">{{ __('admin.categories.index.stats.empty_categories') }}</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Table -->
        @if($categories->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-start">
                    <thead>
                        <tr class="border-b border-primary-gray bg-primary-dark/5">
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">{{ __('admin.categories.table.id') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">{{ __('admin.categories.table.name') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">{{ __('admin.categories.table.slug') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">{{ __('admin.categories.table.products') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">{{ __('admin.categories.table.status') }}</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">{{ __('admin.categories.table.created') }}</th>
                            <th class="px-6 py-4 text-end text-xs font-semibold uppercase tracking-wider text-primary-dark">{{ __('admin.categories.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr class="border-b border-primary-gray/30 hover:bg-primary-dark/5 transition-colors duration-200">
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-sm font-semibold text-primary-dark">#{{ str_pad($category->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-sm font-medium text-primary-dark">{{ $category->display_name }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-sm text-primary-dark/70 font-mono">{{ $category->slug }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-primary-dark/10 text-primary-dark border border-primary-dark/20">
                                        {{ $category->products_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    @if($category->is_active)
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">
                                            {{ __('admin.categories.status.active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-neutral-200 text-neutral-700 border border-neutral-300">
                                            {{ __('admin.categories.status.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-sm text-primary-dark/70">{{ $category->created_at->translatedFormat('M d, Y') }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle text-end">
                                    <div class="flex items-center justify-end gap-2">
                                        <a 
                                            href="{{ route('admin.categories.edit', ['locale' => app()->getLocale(), 'category' => $category]) }}"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-dark text-white hover:bg-primary-dark/90 transition-all duration-300">
                                            {{ __('admin.categories.actions.edit') }}
                                        </a>
                                        <button 
                                            type="button"
                                            @click="deleteModalOpen = true; deleteCategoryId = '{{ $category->id }}'; deleteCategoryName = @js($category->display_name);"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition-all duration-300">
                                            {{ __('admin.categories.actions.delete') }}
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
                {{ $categories->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-16 px-6">
                <svg class="w-16 h-16 text-primary-dark/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <p class="text-lg font-medium text-primary-dark mb-2">{{ __('admin.categories.empty.title') }}</p>
                <p class="text-sm text-primary-dark/60 text-center mb-6">{{ __('admin.categories.empty.subtitle') }}</p>
                <a 
                    href="{{ r('admin.categories.create') }}"
                    class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    {{ __('admin.categories.actions.add_new') }}
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
            <!-- Header -->
            <div>
                <h3 class="text-2xl font-bold text-primary-dark">{{ __('admin.categories.delete_modal.title') }}</h3>
            </div>

            <!-- Content -->
            <div class="space-y-2">
                <p class="text-sm text-neutral-600">
                    {{ __('admin.categories.delete_modal.message') }} <span class="font-semibold text-primary-dark" x-text="deleteCategoryName"></span>? {{ __('admin.categories.delete_modal.cannot_undo') }}
                </p>
                <p class="text-xs text-neutral-500">
                    {{ __('admin.categories.delete_modal.reassign_note') }}
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-3 pt-4">
                <button 
                    type="button"
                    @click="deleteModalOpen = false"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-neutral-300 text-neutral-700 font-semibold text-sm hover:bg-neutral-50 transition-all duration-200">
                    {{ __('admin.categories.actions.cancel') }}
                </button>
                <form 
                    :action="'{{ $categoryDeleteActionTemplate }}'.replace('__CATEGORY__', deleteCategoryId)"
                    method="POST" 
                    style="display: none;"
                    @keydown.enter.window="if (deleteModalOpen) this.submit()"
                    id="deleteForm"
                >
                    @csrf
                    @method('DELETE')
                </form>
                <button 
                    type="button"
                    @click="document.getElementById('deleteForm').submit(); deleteModalOpen = false;"
                    class="flex-1 px-4 py-2.5 rounded-lg bg-red-600 text-white font-semibold text-sm hover:bg-red-700 transition-all duration-200 active:scale-95">
                    {{ __('admin.categories.actions.delete') }}
                </button>
            </div>
        </div>
    </div>

</div>

@endsection
