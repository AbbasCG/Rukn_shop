@extends('layouts.admin', ['title' => 'Edit Category'])

@section('content')
<div class="w-full max-w-3xl mx-auto space-y-6">
    <!-- Header Section -->
    <div>
        <h1 class="text-3xl font-bold text-primary-dark">Edit Category</h1>
        <p class="text-sm text-primary-dark/60 mt-1">Update category information and settings</p>
    </div>

    <!-- Form Card -->
    <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="bg-white rounded-xl shadow-lg p-8 space-y-6">
        @csrf
        @method('PATCH')

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-primary-dark mb-2">Category Name</label>
            <input 
                id="name"
                name="name" 
                type="text"
                value="{{ old('name', $category->name) }}" 
                required
                placeholder="Enter category name"
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Slug -->
        <div>
            <label for="slug" class="block text-sm font-medium text-primary-dark mb-2">Slug (Optional)</label>
            <input 
                id="slug"
                name="slug" 
                type="text"
                value="{{ old('slug', $category->slug) }}"
                placeholder="category-slug"
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            @error('slug')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-primary-dark mb-2">Description</label>
            <textarea 
                id="description"
                name="description" 
                rows="4"
                placeholder="Describe what products belong in this category"
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">{{ old('description', $category->description) }}</textarea>
            @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Status -->
        <div>
            <label class="block text-sm font-medium text-primary-dark mb-2">Status</label>
            <label class="inline-flex items-center gap-3 px-4 py-2.5 rounded-lg border border-primary-gray cursor-pointer hover:bg-primary-gray/50 transition-all">
                <input 
                    type="checkbox" 
                    name="is_active" 
                    value="1" 
                    {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-primary-gray text-primary-dark focus:ring-2 focus:ring-primary-dark/20">
                <span class="text-sm font-medium text-primary-dark">Active</span>
            </label>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4">
            <button 
                type="submit"
                class="flex-1 px-6 py-2.5 bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-dark/90 transition-all duration-300">
                Update Category
            </button>
            <a 
                href="{{ route('admin.categories.index') }}"
                class="flex-1 px-6 py-2.5 border border-primary-gray text-primary-dark rounded-lg font-semibold hover:bg-primary-gray transition-all duration-300 text-center">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
