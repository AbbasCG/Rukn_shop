@extends('layouts.admin', ['title' => __('admin.products.edit.title')])

@section('content')
<div x-data="{ deleteModalOpen: false }" @keydown.escape="deleteModalOpen = false" class="w-full max-w-3xl mx-auto space-y-6">
    <!-- Header Section -->
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">{{ __('admin.products.edit.title') }}</h1>
            <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.products.edit.subtitle') }}</p>
        </div>
        <button
            type="button"
            @click="deleteModalOpen = true"
            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition-all duration-300"
        >
            {{ __('admin.products.actions.delete') }}
        </button>
    </div>

    <!-- Form Card -->
    <form action="{{ route('admin.products.update', ['locale' => app()->getLocale(), 'product' => $product]) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-8 space-y-6">
        @csrf
        @method('PATCH')

        <!-- Grid Layout: 2 columns -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.name') }}</label>
                <input 
                    id="name"
                    name="name" 
                    type="text"
                    value="{{ old('name', $product->name) }}" 
                    required
                    placeholder="{{ __('admin.products.form.placeholders.name') }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.slug_optional') }}</label>
                <input 
                    id="slug"
                    name="slug" 
                    type="text"
                    value="{{ old('slug', $product->slug) }}"
                    placeholder="{{ __('admin.products.form.placeholders.slug') }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                @error('slug')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-primary-dark/60 mt-1">{{ __('admin.products.form.slug_change_safe') }}</p>
                @if($product->slugRedirects->isNotEmpty())
                    <p class="text-xs text-primary-dark/60 mt-1">
                        {{ __('admin.products.form.slug_redirects') }}
                        @foreach($product->slugRedirects as $redirect)
                            <code dir="ltr" class="px-1 rounded bg-primary-gray">{{ $redirect->old_slug }}</code>@unless($loop->last), @endunless
                        @endforeach
                    </p>
                @endif
            </div>

            <!-- Category -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.category') }}</label>
                <select 
                    id="category_id"
                    name="category_id" 
                    required
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                    <option value="">{{ __('admin.products.form.select_category') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->display_name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.price', ['currency' => '$']) }}</label>
                <input 
                    id="price"
                    type="number" 
                    step="0.01" 
                    name="price" 
                    value="{{ old('price', $product->price) }}" 
                    required
                    placeholder="{{ __('admin.products.form.placeholders.price') }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                @error('price')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Stock -->
            <div>
                <label for="stock" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.stock') }}</label>
                <input 
                    id="stock"
                    type="number" 
                    name="stock" 
                    value="{{ old('stock', $product->stock) }}" 
                    required
                    placeholder="{{ __('admin.products.form.placeholders.stock') }}"
                    class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                @error('stock')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.status') }}</label>
                <label class="inline-flex items-center gap-3 px-4 py-2.5 rounded-lg border border-primary-gray cursor-pointer hover:bg-primary-gray/50 transition-all">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-primary-gray text-primary-dark focus:ring-2 focus:ring-primary-dark/20">
                    <span class="text-sm font-medium text-primary-dark">{{ __('admin.products.status.active') }}</span>
                </label>
            </div>
        </div>

        <!-- Short Description (Full width) -->
        <div>
            <label for="short_description" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.short_description') }}</label>
            <textarea 
                id="short_description"
                name="short_description" 
                rows="2"
                placeholder="{{ __('admin.products.form.placeholders.short_description') }}"
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">{{ old('short_description', $product->short_description) }}</textarea>
            @error('short_description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Long Description (Full width) -->
        <div>
            <label for="long_description" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.long_description') }}</label>
            <textarea 
                id="long_description"
                name="long_description" 
                rows="4"
                placeholder="{{ __('admin.products.form.placeholders.long_description') }}"
                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">{{ old('long_description', $product->long_description) }}</textarea>
            @error('long_description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Sizes & colours -->
        @include('admin.products.partials.waiting', ['product' => $product])

        @include('admin.products.partials.sale', ['product' => $product])

        @include('admin.products.partials.variants', ['product' => $product])

        <!-- Specifications (Full width) -->
        @php
            $initialSpecifications = old('specifications', $product->specifications ?? [['key' => '', 'value' => '']]);
            if (empty($initialSpecifications)) {
                $initialSpecifications = [['key' => '', 'value' => '']];
            }
        @endphp
        <div class="space-y-3" x-data="{ specs: @js($initialSpecifications), addSpec() { this.specs.push({ key: '', value: '' }); }, removeSpec(index) { this.specs.splice(index, 1); if (this.specs.length === 0) { this.specs.push({ key: '', value: '' }); } } }">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <label class="block text-sm font-medium text-primary-dark">{{ __('admin.products.form.specifications') }}</label>
                    <p class="text-xs text-primary-dark/60 mt-1">{{ __('admin.products.form.specifications_help') }}</p>
                </div>
                <button type="button" @click="addSpec" class="inline-flex items-center px-3 py-2 rounded-lg border border-primary-gray text-primary-dark text-xs font-semibold hover:bg-primary-gray/60 transition-all">
                    {{ __('admin.products.form.spec_add') }}
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(spec, index) in specs" :key="index">
                    <div class="grid grid-cols-1 md:grid-cols-[1fr,1.5fr,auto] gap-3 items-start">
                        <div>
                            <label class="block text-xs font-semibold text-primary-dark mb-1">{{ __('admin.products.form.spec_key') }}</label>
                            <input
                                type="text"
                                :name="`specifications[${index}][key]`"
                                x-model="spec.key"
                                placeholder="{{ __('admin.products.form.placeholders.spec_key') }}"
                                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-dark mb-1">{{ __('admin.products.form.spec_value') }}</label>
                            <input
                                type="text"
                                :name="`specifications[${index}][value]`"
                                x-model="spec.value"
                                placeholder="{{ __('admin.products.form.placeholders.spec_value') }}"
                                class="w-full px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
                        </div>
                        <div class="md:pt-6">
                            <button type="button" @click="removeSpec(index)" class="inline-flex items-center px-3 py-2 rounded-lg border border-red-200 text-red-600 text-xs font-semibold hover:bg-red-50 transition-all">
                                {{ __('admin.products.form.spec_remove') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Image URL (Full width) - Legacy field -->
        <div x-data="{ previewUrl: '{{ $product->legacyImageUrl() ?? '' }}' }">
            <label for="image_url" class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.image_url_legacy') }}</label>
            
            <!-- Preview -->
            <template x-if="previewUrl">
                <div class="mb-3 p-3 bg-primary-gray rounded-lg">
                    <img :src="previewUrl" alt="{{ __('admin.products.images.image_preview_alt') }}" class="h-32 w-32 object-cover rounded">
                </div>
            </template>

            <div>
                <input 
                    id="image_url"
                    name="image_url" 
                    type="text"
                    value="{{ old('image_url', $product->image_url) }}"
                    placeholder="{{ __('admin.products.form.placeholders.image_url') }}"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-primary-gray bg-white text-primary-dark placeholder-primary-dark/40 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark hover:border-primary-dark/50">
            </div>
            <p class="text-xs text-primary-dark/60 mt-2">{{ __('admin.products.form.legacy_image_help') }}</p>
            @error('image_url')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <!-- Existing Product Images -->
        @if($product->images->count() > 0)
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-primary-dark">{{ __('admin.products.images.existing_images') }} ({{ $product->images->count() }})</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach($product->images as $image)
                <div class="relative group">
                    <img 
                        src="{{ $product->imageUrlFromPath($image->path) }}" 
                        alt="{{ __('admin.products.images.product_image_alt') }}"
                        class="w-full h-32 rounded-lg object-cover shadow-md border border-primary-gray"
                        onerror="this.src='{{ asset(\App\Models\Product::FALLBACK_IMAGE) }}'">
                    
                    <label class="absolute top-1 start-1 bg-primary-dark/80 hover:bg-primary-dark text-white rounded-full p-1.5 transition-all duration-200 shadow-lg cursor-pointer group-hover:bg-primary-dark"
                        title="{{ __('admin.products.images.set_as_primary') }}">
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
                        class="absolute top-1 end-1 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
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

                    <div class="absolute bottom-0 start-0 end-0 bg-gradient-to-t from-black/50 to-transparent p-2 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <p class="text-white text-xs font-medium">
                            @if($image->is_primary) 
                                <span class="inline-block px-2 py-0.5 bg-primary-dark/80 rounded text-xs">{{ __('admin.products.images.primary') }}</span>
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
            <label class="block text-sm font-medium text-primary-dark mb-2">{{ __('admin.products.form.add_more_images') }}</label>
            
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

                <p class="text-base font-medium text-primary-dark">{{ __('admin.products.images.drag_drop') }}</p>
                <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.products.images.click_to_browse') }}</p>
            </div>

            @error('images')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            @error('images.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror

            <div x-show="selectedImages.length > 0" class="mt-6">
                <h3 class="text-sm font-semibold text-primary-dark mb-3">{{ __('admin.products.images.new_images') }} (<span x-text="selectedImages.length"></span>)</h3>
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
                                class="absolute top-1 end-1 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            <div class="absolute bottom-0 start-0 end-0 bg-gradient-to-t from-black/50 to-transparent p-2 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200">
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
                {{ __('admin.products.actions.update') }}
            </button>
            <a 
                href="{{ r('admin.products.index') }}"
                class="flex-1 px-6 py-2.5 border border-primary-gray text-primary-dark rounded-lg font-semibold hover:bg-primary-gray transition-all duration-300 text-center">
                {{ __('admin.products.actions.cancel') }}
            </a>
        </div>
    </form>

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

<div
    id="edit-file-size-labels"
    class="hidden"
    data-bytes="{{ __('admin.products.file_sizes.bytes') }}"
    data-kb="{{ __('admin.products.file_sizes.kb') }}"
    data-mb="{{ __('admin.products.file_sizes.mb') }}"></div>

<script>
function imageUploader() {
    return {
        selectedFiles: [],
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
                    this.selectedFiles.push(file);
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

            this.syncInputFiles();
        },

        removeImage(index) {
            this.selectedFiles.splice(index, 1);
            this.selectedImages.splice(index, 1);
            this.syncInputFiles();
        },

        syncInputFiles() {
            const dataTransfer = new DataTransfer();
            this.selectedFiles.forEach((file) => dataTransfer.items.add(file));
            this.$refs.fileInput.files = dataTransfer.files;
        },

        formatFileSize(bytes) {
            const sizeLabels = document.getElementById('edit-file-size-labels');
            const sizes = [
                sizeLabels?.dataset?.bytes ?? '',
                sizeLabels?.dataset?.kb ?? '',
                sizeLabels?.dataset?.mb ?? '',
            ];
            if (bytes === 0) return '0 ' + sizes[0];
            const k = 1024;
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    }
}
</script>
@endsection
