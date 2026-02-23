@extends('layouts.admin', ['title' => 'Products'])

@section('content')
@php
    $productDeleteActionTemplate = route('admin.products.destroy', ['locale' => app()->getLocale(), 'product' => '__PRODUCT__']);
@endphp

<div x-data="{ 
    deleteModalOpen: false, 
    deleteProductId: null, 
    deleteProductName: '' 
}" @keydown.escape="deleteModalOpen = false" class="w-full max-w-7xl mx-auto space-y-6">
    
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">Products</h1>
            <p class="text-sm text-primary-dark/60 mt-1">Manage your catalog, availability, and pricing</p>
        </div>
        <a 
            href="{{ r('admin.products.create') }}"
            class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-dark text-white text-sm font-semibold hover:bg-primary-dark/90 transition-all duration-300">
            + Add New Product
        </a>
    </div>

    <!-- Search & Filter Toolbar -->
    <form method="GET" class="bg-white rounded-xl shadow-lg p-6 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Search Input -->
            <div>
                <label for="q" class="block text-sm font-medium text-primary-dark mb-2">Search</label>
                <input 
                    type="text"
                    id="q"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Product name or SKU..."
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>

            <!-- Category Filter -->
            <div>
                <label for="category" class="block text-sm font-medium text-primary-dark mb-2">Category</label>
                <select 
                    id="category"
                    name="category"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-primary-dark mb-2">Status</label>
                <select 
                    id="status"
                    name="status"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">All Products</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>

            <!-- Price Range -->
            <div>
                <label for="price_min" class="block text-sm font-medium text-primary-dark mb-2">Min Price</label>
                <input 
                    type="number"
                    id="price_min"
                    name="price_min"
                    value="{{ request('price_min') }}"
                    placeholder="€0"
                    step="0.01"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>

            <!-- Max Price -->
            <div>
                <label for="price_max" class="block text-sm font-medium text-primary-dark mb-2">Max Price</label>
                <input 
                    type="number"
                    id="price_max"
                    name="price_max"
                    value="{{ request('price_max') }}"
                    placeholder="€999"
                    step="0.01"
                    class="w-full px-4 py-2 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
            <button 
                type="submit"
                class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                Search
            </button>
            <a 
                href="{{ r('admin.products.index') }}"
                class="px-4 py-2 bg-white border border-primary-gray text-primary-dark font-medium rounded-lg hover:bg-primary-dark/5 transition-all duration-300">
                Reset
            </a>
        </div>
    </form>

    <!-- Product Statistics Cards -->
    @php
        $totalProducts = \App\Models\Product::count();
        $activeProducts = \App\Models\Product::where('is_active', true)->count();
        $lowStockProducts = \App\Models\Product::where('stock', '<', 5)->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Total Products Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">Total Products</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $totalProducts }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">In catalog</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Products Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">Active Products</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $activeProducts }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">Published</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Low Stock Products Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-dark/60">Low Stock</p>
                    <p class="text-3xl font-bold text-primary-dark mt-2">{{ $lowStockProducts }}</p>
                    <p class="text-xs text-primary-dark/50 mt-1">Need reorder</p>
                </div>
                <div class="bg-primary-dark/10 rounded-full p-3">
                    <svg class="w-8 h-8 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M6.75 15H4.5A2.25 2.25 0 012.25 12.75V9m13.5 5.25h2.25A2.25 2.25 0 0021.75 12.75V9"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Table -->
        @if($products->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-primary-gray bg-primary-dark/5">
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">ID</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">Image</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">Name</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">Category</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">Price</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">Stock</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">Status</th>
                            <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-primary-dark">Created</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-primary-dark">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr class="border-b border-primary-gray/30 hover:bg-primary-dark/5 transition-colors duration-200">
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-sm font-semibold text-primary-dark">#{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover border border-primary-gray" onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'">
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-sm font-medium text-primary-dark">{{ $product->name }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-sm text-primary-dark/70">{{ $product->category->name ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-sm font-semibold text-primary-dark">€{{ number_format($product->price, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    @if($product->stock <= 0)
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                            Out of stock
                                        </span>
                                    @elseif($product->stock < 5)
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                            {{ $product->stock }} left
                                        </span>
                                    @else
                                        <span class="text-sm text-primary-dark/70">{{ $product->stock }} units</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    @if($product->is_active)
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-neutral-200 text-neutral-700 border border-neutral-300">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-sm text-primary-dark/70">{{ $product->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="px-6 py-4 align-middle text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a 
                                            href="{{ route('admin.products.edit', ['locale' => app()->getLocale(), 'product' => $product]) }}"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-dark text-white hover:bg-primary-dark/90 transition-all duration-300">
                                            Edit
                                        </a>
                                        <a 
                                            href="{{ route('products.show', ['locale' => app()->getLocale(), 'product' => $product]) }}"
                                            target="_blank"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg border border-primary-gray text-primary-dark hover:bg-primary-dark/5 transition-all duration-300">
                                            View
                                        </a>
                                        <button 
                                            type="button"
                                            @click="deleteModalOpen = true; deleteProductId = '{{ $product->id }}'; deleteProductName = @js($product->name)"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition-all duration-300">
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
                {{ $products->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="flex flex-col items-center justify-center py-16 px-6">
                <svg class="w-16 h-16 text-primary-dark/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-lg font-medium text-primary-dark mb-2">No products found</p>
                <p class="text-sm text-primary-dark/60 text-center mb-6">Try adjusting your filters or add a new product to get started</p>
                <a 
                    href="{{ r('admin.products.create') }}"
                    class="px-4 py-2 bg-primary-dark text-white font-medium rounded-lg hover:bg-primary-dark/90 transition-all duration-300">
                    Add New Product
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
                <h3 class="text-2xl font-bold text-primary-dark">{{ __('admin_products.delete_title') }}</h3>
            </div>

            <!-- Content -->
            <div class="space-y-2">
                <p class="text-sm text-neutral-600">
                    {{ __('admin_products.delete_message') }} <span class="font-semibold text-primary-dark" x-text="deleteProductName"></span>?
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-3 pt-4">
                <button 
                    type="button"
                    @click="deleteModalOpen = false"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-neutral-300 text-neutral-700 font-semibold text-sm hover:bg-neutral-50 transition-all duration-200">
                    {{ __('admin_products.cancel') }}
                </button>
                <form 
                    :action="'{{ $productDeleteActionTemplate }}'.replace('__PRODUCT__', deleteProductId)"
                    method="POST" 
                    style="display: none;"
                    @keydown.enter.window="if (deleteModalOpen) this.submit()"
                    id="deleteProductForm"
                >
                    @csrf
                    @method('DELETE')
                </form>
                <button 
                    type="button"
                    @click="document.getElementById('deleteProductForm').submit(); deleteModalOpen = false;"
                    class="flex-1 px-4 py-2.5 rounded-lg bg-red-600 text-white font-semibold text-sm hover:bg-red-700 transition-all duration-200 active:scale-95">
                    {{ __('admin_products.confirm_delete') }}
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
