@extends('layouts.admin', ['title' => 'Create Product'])

@section('content')
<div class="w-full max-w-3xl mx-auto space-y-6">
    <!-- Header Section -->
    <div>
        <h1 class="text-3xl font-bold text-primary-dark">Create Product</h1>
        <p class="text-sm text-primary-dark/60 mt-1">Fill in the details below to add a new product to your catalog</p>
    </div>

    <!-- Form Card -->
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-8 space-y-6">
        @csrf

        <!-- Grid Layout: 2 columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-primary-dark mb-2">Product Name</label>
                <input 
                    id="name"
                    name="name" 
                    type="text"
                    value="{{ old('name') }}" 
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
                    value="{{ old('slug') }}"
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
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
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
                    value="{{ old('price') }}" 
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
                    value="{{ old('stock') }}" 
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
                        {{ old('is_active', true) ? 'checked' : '' }}
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
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">{{ old('short_description') }}</textarea>
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
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">{{ old('long_description') }}</textarea>
            @error('long_description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Image URL (Full width) - Legacy field -->
        <div x-data="{ imageUrlUpload: false }">
            <label for="image_url" class="block text-sm font-medium text-primary-dark mb-2">Image URL (Legacy)</label>
            <div class="flex gap-2">
                <input 
                    id="image_url"
                    name="image_url" 
                    type="text"
                    value="{{ old('image_url') }}"
                    placeholder="https://example.com/image.jpg"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                <button 
                    type="button"
                    @click="$refs.imageUrlInput.click()"
                    class="px-4 py-2.5 bg-primary-dark text-white rounded-lg font-semibold hover:bg-primary-dark/90 transition-all duration-300">
                    Upload
                </button>
                <input 
                    type="file"
                    x-ref="imageUrlInput"
                    accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                    @change="
                        const file = $refs.imageUrlInput.files[0];
                        if (file && file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                document.getElementById('image_url').value = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    "
                    class="hidden">
            </div>
            @error('image_url')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Drag & Drop Image Upload (Full width) -->
        <div x-data="imageUploader()" class="space-y-4">
            <label class="block text-sm font-medium text-primary-dark mb-2">Product Images</label>
            
            <!-- Dropzone -->
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

            <!-- Preview Grid -->
            <div x-show="selectedImages.length > 0" class="mt-6">
                <h3 class="text-sm font-semibold text-primary-dark mb-3">Selected Images (<span x-text="selectedImages.length"></span>)</h3>
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
                Create Product
            </button>
            <a 
                href="{{ route('admin.products.index') }}"
                class="flex-1 px-6 py-2.5 border border-primary-gray text-primary-dark rounded-lg font-semibold hover:bg-primary-gray transition-all duration-300 text-center">
                Cancel
            </a>
        </div>
    </form>
</div>

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
@endsection
