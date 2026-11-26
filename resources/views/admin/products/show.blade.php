@extends('layouts.admin', ['title' => __('admin.products.show.title')])

@section('content')
<div x-data="{ deleteModalOpen: false }" @keydown.escape="deleteModalOpen = false" class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-semibold text-primary-800 dark:text-primary-800">{{ $product->name }}</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.edit', ['locale' => app()->getLocale(), 'product' => $product]) }}" class="px-4 py-2 bg-primary-800 text-white rounded-lg hover:bg-primary-700 active:bg-primary-900 transition-all\">{{ __('admin.products.actions.edit') }}</a>
            <button
                type="button"
                @click="deleteModalOpen = true"
                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition-all duration-300"
            >
                {{ __('admin.products.actions.delete') }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-white rounded-xl p-4 shadow border border-neutral-200 dark:border-neutral-300">
            <img src="{{ $product->primaryImageUrl() }}" class="w-full rounded-lg" alt="{{ $product->name }}" onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'">
        </div>
        <div class="bg-white dark:bg-white rounded-xl p-4 shadow border border-neutral-200 dark:border-neutral-300 space-y-3 text-primary-800 dark:text-primary-800">
            <p><span class="font-semibold">{{ __('admin.products.show.fields.price') }}:</span> {{ money($product->price) }}</p>
            <p><span class="font-semibold">{{ __('admin.products.show.fields.stock') }}:</span> {{ $product->stock }}</p>
            <p><span class="font-semibold">{{ __('admin.products.show.fields.category') }}:</span> {{ $product->category->display_name ?? '-' }}</p>
            <p><span class="font-semibold">{{ __('admin.products.show.fields.status') }}:</span> 
                <span class="px-2 py-1 text-xs rounded-full font-medium {{ $product->is_active ? 'bg-success-light/20 text-success dark:bg-success-light/20 dark:text-success-dark' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300' }}">
                    {{ $product->is_active ? __('admin.products.status.active') : __('admin.products.status.hidden') }}
                </span>
            </p>
            <p class="text-sm font-semibold text-primary-800 dark:text-primary-800 border-t border-neutral-200 dark:border-neutral-300 pt-3">{{ __('admin.products.show.fields.short_description') }}</p>
            <p class="text-sm text-primary-600 dark:text-primary-600 border-t border-neutral-200 dark:border-neutral-300 pt-3">{{ $product->short_description }}</p>
            <p class="text-sm font-semibold text-primary-800 dark:text-primary-800">{{ __('admin.products.show.fields.long_description') }}</p>
            <p class="text-sm text-primary-600 dark:text-primary-600">{{ $product->long_description }}</p>
        </div>
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
                <h3 class="text-2xl font-bold text-primary-dark">{{ __('admin.products.delete_modal.title') }}</h3>
            </div>
            <div class="space-y-2">
                <p class="text-sm text-neutral-600">
                    {{ __('admin.products.delete_modal.message') }} <span class="font-semibold text-primary-dark">{{ $product->name }}</span>?
                </p>
            </div>
            <div class="flex items-center gap-3 pt-4">
                <button 
                    type="button"
                    @click="deleteModalOpen = false"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-neutral-300 text-neutral-700 font-semibold text-sm hover:bg-neutral-50 transition-all duration-200">
                    {{ __('admin.products.actions.cancel') }}
                </button>
                <form
                    action="{{ route('admin.products.destroy', ['locale' => app()->getLocale(), 'product' => $product]) }}"
                    method="POST"
                    style="display: none;"
                    id="deleteProductForm"
                >
                    @csrf
                    @method('DELETE')
                </form>
                <button 
                    type="button"
                    @click="document.getElementById('deleteProductForm').submit(); deleteModalOpen = false;"
                    class="flex-1 px-4 py-2.5 rounded-lg bg-red-600 text-white font-semibold text-sm hover:bg-red-700 transition-all duration-200 active:scale-95">
                    {{ __('admin.products.actions.confirm_delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
