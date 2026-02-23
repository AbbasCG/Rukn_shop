<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'short_description' => 'required|string',
            'long_description' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['is_active'] = $request->boolean('is_active');

        // Validate image_url is a proper URL, not Base64
        if (!empty($validated['image_url']) && strpos($validated['image_url'], 'data:') === 0) {
            // If Base64 data is submitted, clear it to force file upload usage
            $validated['image_url'] = null;
        }

        // Remove images array from validated data before creating product
        unset($validated['images']);

        $product = Product::create($validated);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $this->storeProductImages($product, $request->file('images'));
        }

        return redirect()->route('admin.products.index')->with('success', __('admin_products.created_success'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $locale, string $product)
    {
        $product = Product::with('category', 'images')->findOrFail($product);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $locale, string $product)
    {
        $product = Product::with('images')->findOrFail($product);
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $locale, string $product)
    {
        $product = Product::findOrFail($product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'short_description' => 'required|string',
            'long_description' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images_to_delete' => 'nullable|array',
            'images_to_delete.*' => 'nullable|integer|exists:product_images,id',
            'primary_image_id' => 'nullable|integer|exists:product_images,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? $product->slug;
        $validated['is_active'] = $request->boolean('is_active');

        // Validate image_url is a proper URL, not Base64
        if (!empty($validated['image_url']) && strpos($validated['image_url'], 'data:') === 0) {
            // If Base64 data is submitted, clear it to force file upload usage
            $validated['image_url'] = null;
        }

        // Remove arrays from validated data
        unset($validated['images'], $validated['images_to_delete']);

        $product->update($validated);

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

        return redirect()->route('admin.products.index')->with('success', __('admin_products.updated_success'));
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

            return back()->with('error', __('admin_products.delete_failed'));
        }

        return redirect()->route('admin.products.index')->with('success', __('admin_products.deleted_success'));
    }

    /**
     * Store product images.
     */
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
}

