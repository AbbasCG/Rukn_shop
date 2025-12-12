<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Product::with('category');

        if (request('q')) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . request('q') . '%')
                    ->orWhere('slug', 'like', '%' . request('q') . '%');
            });
        }

        if (request('category')) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', request('category'));
            });
        }

        if (request('status') !== null) {
            $query->where('is_active', request('status') === 'active');
        }

        $sort = request('sort', 'newest');
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

        $products = $query->paginate(12)->withQueryString();
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
            'image_url' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['is_active'] = $request->boolean('is_active');

        // Remove images array from validated data before creating product
        unset($validated['images']);

        $product = Product::create($validated);

        // Handle multiple image uploads
        if ($request->hasFile('images')) {
            $this->storeProductImages($product, $request->file('images'));
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category', 'images')->findOrFail($id);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::with('images')->findOrFail($id);
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'short_description' => 'required|string',
            'long_description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images_to_delete' => 'nullable|array',
            'images_to_delete.*' => 'nullable|integer|exists:product_images,id',
            'primary_image_id' => 'nullable|integer|exists:product_images,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? $product->slug;
        $validated['is_active'] = $request->boolean('is_active');

        // Remove arrays from validated data
        unset($validated['images'], $validated['images_to_delete']);

        $product->update($validated);

        // Handle image deletions
        if ($request->has('images_to_delete') && !empty($request->input('images_to_delete'))) {
            $this->deleteProductImages($request->input('images_to_delete'));
            $product->load('images');
        }

        // Handle primary image selection
        if ($request->filled('primary_image_id')) {
            ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
            ProductImage::where('id', $request->input('primary_image_id'))
                ->where('product_id', $product->id)
                ->update(['is_primary' => true]);
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

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::with('images')->findOrFail($id);
        
        // Delete all associated images from storage
        foreach ($product->images as $image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        }
        
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
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
            // Delete from database
            $image->delete();
        }
    }
}

