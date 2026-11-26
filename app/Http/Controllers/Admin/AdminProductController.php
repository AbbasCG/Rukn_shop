<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,name'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        $query = Product::with('category', 'images');

        $hasSkuColumn = Schema::hasColumn('products', 'sku');

        if (!empty($validated['q'])) {
            $tokens = preg_split('/\s+/u', trim($validated['q'])) ?: [];

            foreach ($tokens as $token) {
                $query->where(function ($subQuery) use ($token, $hasSkuColumn) {
                    $likeToken = '%' . $token . '%';

                    $subQuery->where('name', 'like', $likeToken)
                        ->orWhere('slug', 'like', $likeToken)
                        ->orWhereHas('category', function ($categoryQuery) use ($likeToken) {
                            $categoryQuery->where('name', 'like', $likeToken);
                        });

                    if ($hasSkuColumn) {
                        $subQuery->orWhere('sku', 'like', $likeToken);
                    }
                });
            }
        }

        if (!empty($validated['category'])) {
            $query->whereHas('category', function ($q) use ($validated) {
                $q->where('slug', $validated['category']);
            });
        }

        if (!empty($validated['status'])) {
            $query->where('is_active', $validated['status'] === 'active');
        }

        if (isset($validated['price_min'])) {
            $query->where('price', '>=', $validated['price_min']);
        }

        if (isset($validated['price_max'])) {
            $query->where('price', '<=', $validated['price_max']);
        }

        $sort = $validated['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->appends($request->query());
        $categories = Category::select('id', 'name', 'slug')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        $categories = Category::select('id', 'name')->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->normalizeSlugInput($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'short_description' => 'required|string',
            'long_description' => 'nullable|string',
            'specifications' => 'nullable|array',
            'specifications.*.key' => 'nullable|string|max:100',
            'specifications.*.value' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'sometimes|boolean',
        ] + $this->variantRules());

        $validated['slug'] = $validated['slug'] ?? Product::uniqueSlug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        // Validate image_url is a proper URL, not Base64
        if (!empty($validated['image_url']) && strpos($validated['image_url'], 'data:') === 0) {
            // If Base64 data is submitted, clear it to force file upload usage
            $validated['image_url'] = null;
        }

        // Remove images array from validated data before creating product
        unset($validated['images']);

        $validated['specifications'] = $this->normalizeSpecifications($request->input('specifications', []));
        if (empty($validated['specifications'])) {
            $validated['specifications'] = null;
        }

        $variantInput = \Illuminate\Support\Arr::only($validated, ['size_system', 'sizes', 'colors', 'variants']);
        unset($validated['size_system'], $validated['sizes'], $validated['colors'], $validated['variants']);
        $product = Product::create($validated);
        $this->syncVariants($product, $variantInput);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $this->storeProductImages($product, $request->file('images'));
        }

        return redirect()->route('admin.products.index')->with('success', __('admin.products.messages.created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $locale, string $product)
    {
        $product = $this->findProduct($product)->load('category', 'images');
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $locale, string $product)
    {
        $product = $this->findProduct($product)->load('images', 'slugRedirects', 'variants');
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $locale, string $product)
    {
        $product = $this->findProduct($product);
        $this->normalizeSlugInput($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'short_description' => 'required|string',
            'long_description' => 'nullable|string',
            'specifications' => 'nullable|array',
            'specifications.*.key' => 'nullable|string|max:100',
            'specifications.*.value' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images_to_delete' => 'nullable|array',
            'images_to_delete.*' => 'nullable|integer|exists:product_images,id',
            'primary_image_id' => 'nullable|integer|exists:product_images,id',
            'is_active' => 'sometimes|boolean',
        ] + $this->variantRules());

        $validated['slug'] = $validated['slug'] ?? $product->slug;
        $validated['is_active'] = $request->boolean('is_active');

        // Validate image_url is a proper URL, not Base64
        if (!empty($validated['image_url']) && strpos($validated['image_url'], 'data:') === 0) {
            // If Base64 data is submitted, clear it to force file upload usage
            $validated['image_url'] = null;
        }

        // Remove arrays from validated data
        unset($validated['images'], $validated['images_to_delete']);

        $validated['specifications'] = $this->normalizeSpecifications($request->input('specifications', []));
        if (empty($validated['specifications'])) {
            $validated['specifications'] = null;
        }

        $variantInput = \Illuminate\Support\Arr::only($validated, ['size_system', 'sizes', 'colors', 'variants']);
        unset($validated['size_system'], $validated['sizes'], $validated['colors'], $validated['variants']);
        $product->update($validated);
        $this->syncVariants($product, $variantInput);

        // Handle image deletions (only images that belong to current product)
        if ($request->has('images_to_delete') && !empty($request->input('images_to_delete'))) {
            $imageIdsToDelete = ProductImage::where('product_id', $product->id)
                ->whereIn('id', $request->input('images_to_delete'))
                ->pluck('id')
                ->all();

            if (!empty($imageIdsToDelete)) {
                $this->deleteProductImages($imageIdsToDelete);
            }

            $product->load('images');
        }

        // Handle primary image selection (must belong to current product)
        if ($request->filled('primary_image_id')) {
            $primaryImage = ProductImage::where('id', $request->input('primary_image_id'))
                ->where('product_id', $product->id)
                ->first();

            if ($primaryImage) {
                ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
                $primaryImage->update(['is_primary' => true]);
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $this->storeProductImages($product, $request->file('images'));
            $product->load('images');
        }

        // Ensure at least one primary image remains
        if (!$product->images()->where('is_primary', true)->exists() && $product->images()->exists()) {
            $product->images()->orderBy('id')->first()->update(['is_primary' => true]);
        }

        return redirect()->route('admin.products.index')->with('success', __('admin.products.messages.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $locale, Product $product)
    {
        $product->load('images');

        try {
            // Delete all associated images from storage
            foreach ($product->images as $image) {
                if (Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
            }

            $product->delete();
        } catch (QueryException $exception) {
            Log::error('Product delete failed due to database constraint.', [
                'product_id' => $product->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', __('admin.products.messages.delete_failed'));
        }

        return redirect()->route('admin.products.index')->with('success', __('admin.products.messages.deleted'));
    }

    /**
     * Store product images.
     */
    /**
     * "Black Abaya" -> "black-abaya" before validation, so the unique rule
     * checks the value that is actually stored. Empty stays empty (generated later).
     */
    private function normalizeSlugInput(Request $request): void
    {
        $slug = trim((string) $request->input('slug', ''));
        $request->merge(['slug' => $slug === '' ? null : (Str::slug($slug) ?: null)]);
    }

    /**
     * Admin URLs carry the product slug (its route key); old id-based links still work.
     */
    private function findProduct(string $key): Product
    {
        return (new Product())->resolveRouteBinding($key) ?? abort(404);
    }

    private function storeProductImages(Product $product, array $files)
    {
        $isFirstImage = $product->images()->count() === 0;

        foreach ($files as $file) {
            if ($file && $file->isValid()) {
                // Store file in public disk under products/{product_id}
                $path = $file->store("products/{$product->id}", 'public');

                // Create product image record
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'is_primary' => $isFirstImage, // First uploaded image is primary
                ]);

                $isFirstImage = false;
            }
        }
    }

    /**
     * Delete product images.
     */
    private function deleteProductImages(array $imageIds)
    {
        $images = ProductImage::whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            // Delete from storage
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            
            $image->delete();
        }
    }

    private function normalizeSpecifications(array $specifications): array
    {
        return collect($specifications)
            ->map(function ($spec) {
                $key = is_array($spec) ? trim((string) ($spec['key'] ?? '')) : '';
                $value = is_array($spec) ? trim((string) ($spec['value'] ?? '')) : '';

                return [
                    'key' => $key,
                    'value' => $value,
                ];
            })
            ->filter(function ($spec) {
                return $spec['key'] !== '' && $spec['value'] !== '';
            })
            ->values()
            ->all();
    }

    /**
     * Validation for the sizes & colours editor.
     */
    private function variantRules(): array
    {
        return [
            'size_system' => ['nullable', Rule::in(Product::SIZE_SYSTEMS)],
            'sizes' => ['nullable', 'array', 'max:30'],
            'sizes.*' => ['string', 'max:50', 'distinct'],
            'colors' => ['nullable', 'array', 'max:30'],
            'colors.*.key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/', 'distinct'],
            'colors.*.hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'colors.*.names' => ['required', 'array', function ($attribute, $value, $fail) {
                if (! collect($value)->only(['nl', 'en', 'ar'])->filter(fn ($name) => trim((string) $name) !== '')->count()) {
                    $fail(__('admin.variants.color_name_required'));
                }
            }],
            'colors.*.names.*' => ['nullable', 'string', 'max:50'],
            'variants' => ['nullable', 'array', 'max:900'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.color_key' => ['nullable', 'string', 'max:50'],
            'variants.*.stock' => ['required', 'integer', 'min:0'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Save sizes/colours on the product and make product_variants match:
     * one row per size × colour, existing rows updated, removed ones deleted
     * (past orders keep their variant label). products.stock becomes the sum.
     */
    private function syncVariants(Product $product, array $input): void
    {
        $system = $input['size_system'] ?? null;
        $sizes = $system ? array_values(array_unique(array_map(fn ($s) => trim((string) $s), $input['sizes'] ?? []))) : [];
        $sizes = array_values(array_filter($sizes, fn ($s) => $s !== ''));
        if ($system === 'lengths' || $system === 'letters') {
            $list = $system === 'lengths' ? Product::SIZE_LENGTHS : Product::SIZE_LETTERS;
            $sizes = array_values(array_intersect($list, $sizes)); // only known sizes, in list order
        }

        $colors = collect($input['colors'] ?? [])->map(fn ($color) => [
            'key' => $color['key'],
            'hex' => $color['hex'] ?? null,
            'names' => collect($color['names'] ?? [])->only(['nl', 'en', 'ar'])
                ->map(fn ($name) => trim((string) $name))->filter()->all(),
        ])->values()->all();

        $product->forceFill([
            'size_system' => $sizes ? $system : null,
            'sizes' => $sizes ?: null,
            'colors' => $colors ?: null,
        ])->saveQuietly();

        if (! $sizes && ! $colors) {
            $product->variants()->delete();
            return;
        }

        $submitted = collect($input['variants'] ?? [])
            ->keyBy(fn ($v) => ($v['size'] ?? '') . '|' . ($v['color_key'] ?? ''));

        $keep = [];
        foreach ($sizes ?: [null] as $size) {
            foreach (array_column($colors, 'key') ?: [null] as $colorKey) {
                $row = $submitted->get(($size ?? '') . '|' . ($colorKey ?? ''), []);
                $variant = $product->variants()->firstOrNew(['size' => $size, 'color_key' => $colorKey]);
                $variant->stock = max(0, (int) ($row['stock'] ?? 0));
                $variant->price = isset($row['price']) && $row['price'] !== '' ? $row['price'] : null;
                $variant->save();
                $keep[] = $variant->id;
            }
        }

        $product->variants()->whereNotIn('id', $keep)->delete();
        $product->unsetRelation('variants');
        $product->syncStockFromVariants();
    }
}
