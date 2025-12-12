@extends('layouts.admin', ['title' => 'Product Details'])

@section('content')
<div class="max-w-5xl mx-auto flex items-center justify-between mb-4" style="font-family: 'Signika', sans-serif;">
    <h2 class="text-2xl font-semibold text-primary-800 dark:text-primary-800">{{ $product->name }}</h2>
    <a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 bg-primary-800 text-white rounded-lg hover:bg-primary-700 active:bg-primary-900 transition-all\">Edit</a>
</div>

<div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-white rounded-xl p-4 shadow border border-neutral-200 dark:border-neutral-300">
        @if($product->image_url)
            <img src="{{ asset($product->image_url) }}" class="w-full rounded-lg" alt="">
        @else
            <div class="w-full h-64 bg-primary-100 dark:bg-primary-100 rounded-lg border border-neutral-200 dark:border-neutral-300"></div>
        @endif
    </div>
    <div class="bg-white dark:bg-white rounded-xl p-4 shadow border border-neutral-200 dark:border-neutral-300 space-y-3 text-primary-800 dark:text-primary-800">
        <p><span class="font-semibold">Price:</span> €{{ number_format($product->price,2) }}</p>
        <p><span class="font-semibold">Stock:</span> {{ $product->stock }}</p>
        <p><span class="font-semibold">Category:</span> {{ $product->category->name ?? '-' }}</p>
        <p><span class="font-semibold">Status:</span> 
            <span class="px-2 py-1 text-xs rounded-full font-medium {{ $product->is_active ? 'bg-success-light/20 text-success dark:bg-success-light/20 dark:text-success-dark' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300' }}">
                {{ $product->is_active ? 'Active' : 'Hidden' }}
            </span>
        </p>
        <p class="text-sm text-primary-600 dark:text-primary-600 border-t border-neutral-200 dark:border-neutral-300 pt-3">{{ $product->short_description }}</p>
        <p class="text-sm text-primary-600 dark:text-primary-600">{{ $product->long_description }}</p>
    </div>
</div>
@endsection
