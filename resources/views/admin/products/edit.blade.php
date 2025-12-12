@extends('layouts.admin', ['title' => 'Edit Product'])

@section('content')
<div class="w-full max-w-3xl mx-auto space-y-6">
    <!-- Header Section -->
    <div>
        <h1 class="text-3xl font-bold text-primary-dark">Edit Product</h1>
    <script>
    function imageUploader() {
        return {
            selectedImages: [],
            dragover: false,
            handleDrop(e) {
                this.dragover = false;
                const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                this.processFiles(files);
            },
            handleFiles() {
                const files = Array.from(this.$refs.fileInput.files);
                this.processFiles(files);
            },
            processFiles(files) {
                files.forEach(file => {
                    if (file.type.startsWith('image/') && file.size <= 5120 * 1024) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.selectedImages.push({
                                file: file,
                                preview: e.target.result
                            });
                        };
                        reader.readAsDataURL(file);
                    }
                });
            },
            removeImage(index) {
                this.selectedImages.splice(index, 1);
                this.$refs.fileInput.value = '';
            },
            formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }
        }
    }
    </script>
        <p class="text-sm text-primary-dark/60 mt-1">Update product information and availability</p>
    </div>

    <!-- Form Card -->
    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-8 space-y-6">
        @csrf
        @method('PATCH')

        <!-- Grid Layout: 2 columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-primary-dark mb-2">Product Name</label>
                <input 
                    id="name"
                    name="name" 
                    type="text"
                    value="{{ old('name', $product->name) }}" 
                    required
                    placeholder="Enter product name"
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
                    value="{{ old('slug', $product->slug) }}"
                    placeholder="product-slug"
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                @error('slug')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Category -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-primary-dark mb-2">Category</label>
                <select 
                    id="category_id"
                    name="category_id" 
                    required
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-primary-dark mb-2">Price ($)</label>
                <input 
                    id="price"
                    type="number" 
                    step="0.01" 
                    name="price" 
                    value="{{ old('price', $product->price) }}" 
                    required
                    placeholder="0.00"
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                @error('price')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Stock -->
            <div>
                <label for="stock" class="block text-sm font-medium text-primary-dark mb-2">Stock Quantity</label>
                <input 
                    id="stock"
                    type="number" 
                    name="stock" 
                    value="{{ old('stock', $product->stock) }}" 
                    required
                    placeholder="0"
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                @error('stock')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-primary-dark mb-2">Status</label>
                <label class="inline-flex items-center gap-3 px-4 py-2.5 rounded-lg border border-primary-gray cursor-pointer hover:bg-primary-gray/50 transition-all">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-primary-gray text-primary-dark focus:ring-2 focus:ring-primary-dark/20">
                    <span class="text-sm font-medium text-primary-dark">Active</span>
                </label>
            </div>
        </div>

        <!-- Short Description (Full width) -->
        <div>
            <label for="short_description" class="block text-sm font-medium text-primary-dark mb-2">Short Description</label>
            <textarea 
                id="short_description"
                name="short_description" 
                rows="2"
                placeholder="A brief description of the product"
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">{{ old('short_description', $product->short_description) }}</textarea>
            @error('short_description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Long Description (Full width) -->
        <div>
            <label for="long_description" class="block text-sm font-medium text-primary-dark mb-2">Full Description</label>
            <textarea 
                id="long_description"
                name="long_description" 
                rows="4"
                placeholder="Detailed product description"
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">{{ old('long_description', $product->long_description) }}</textarea>
            @error('long_description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Image URL (Full width) -->
        <div>
            <label for="image_url" class="block text-sm font-medium text-primary-dark mb-2">Image URL</label>
            <input 
                id="image_url"
                name="image_url" 
                type="text"
                value="{{ old('image_url', $product->image_url) }}"
                placeholder="https://example.com/image.jpg"
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            @error('image_url')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Existing Product Images -->
        @if($product->images->count() > 0)
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-primary-dark">Existing Images ({{ $product->images->count() }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach($product->images as $image)
                <div class="relative group">
                    <img 
                        src="{{ asset('storage/' . $image->path) }}" 
                        alt="Product image"
                        class="w-full h-32 rounded-lg object-cover shadow-md border border-primary-gray">
                    
                    <label class="absolute top-1 left-1 bg-primary-dark/80 hover:bg-primary-dark text-white rounded-full p-1.5 transition-all duration-200 shadow-lg cursor-pointer group-hover:bg-primary-dark"
                        title="Set as primary">
                        <input 
                            type="radio" 
                            name="primary_image_id"
                            value="{{ $image->id }}"
                            {{ $image->is_primary ? 'checked' : '' }}
                            class="hidden">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </label>

                    <button 
                        type="button"
                        onclick="document.getElementById('delete-image-{{ $image->id }}').checked = !document.getElementById('delete-image-{{ $image->id }}').checked; this.classList.toggle('bg-red-700'); this.classList.toggle('bg-red-600');"
                        class="absolute top-1 right-1 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>

                    <input 
                        type="checkbox" 
                        id="delete-image-{{ $image->id }}"
                        name="images_to_delete[]"
                        value="{{ $image->id }}"
                        class="hidden">

                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent p-2 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <p class="text-white text-xs font-medium">
                            @if($image->is_primary) 
                                <span class="inline-block px-2 py-0.5 bg-primary-dark/80 rounded text-xs">Primary</span>
                            @endif
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Drag & Drop Image Upload -->
        <div x-data="imageUploader()" class="space-y-4">
            <label class="block text-sm font-medium text-primary-dark mb-2">Add More Images</label>
            
            <div 
                @dragover.prevent="dragover = true"
                @dragleave.prevent="dragover = false"
                @drop.prevent="handleDrop"
                :class="dragover ? 'border-primary-dark bg-primary-dark/5' : 'border-primary-gray hover:border-primary-dark/50'"
                class="relative border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all duration-300"
                @click="$refs.fileInput.click()">
                
                <input 
                    type="file"
                    ref="fileInput"
                    x-ref="fileInput"
                    multiple
                    accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                    @change="handleFiles"
                    class="hidden"
                    name="images[]">

                <svg class="mx-auto h-12 w-12 text-primary-dark/40 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>

                <p class="text-base font-medium text-primary-dark">Drag & drop images here</p>
                <p class="text-sm text-primary-dark/60 mt-1">or click to browse (JPG, PNG, WebP up to 5MB)</p>
            </div>

            @error('images')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            @error('images.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror

            <div x-show="selectedImages.length > 0" class="mt-6">
                <h3 class="text-sm font-semibold text-primary-dark mb-3">New Images (<span x-text="selectedImages.length"></span>)</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <template x-for="(image, index) in selectedImages" :key="index">
                        <div class="relative group">
                            <img 
                                :src="image.preview" 
                                :alt="image.file.name"
                                class="w-full h-32 rounded-lg object-cover shadow-md border border-primary-gray">
                            
                            <button 
                                type="button"
                                @click="removeImage(index)"
                                class="absolute top-1 right-1 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/50 to-transparent p-2 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <p class="text-white text-xs truncate" :title="image.file.name" x-text="image.file.name"></p>
                                <p class="text-gray-200 text-xs" x-text="formatFileSize(image.file.size)"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4">
            <button 
                type="submit"
                class="flex-1 px-6 py-2.5 bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-dark/90 transition-all duration-300">
                Update Product
            </button>
            <a 
                href="{{ route('admin.products.index') }}"
                class="flex-1 px-6 py-2.5 border border-primary-gray text-primary-dark rounded-lg font-semibold hover:bg-primary-gray transition-all duration-300 text-center">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
