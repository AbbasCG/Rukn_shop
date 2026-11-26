<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


class AdminCategoryController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware(['auth', 'isAdmin']);
    // }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort' => ['nullable', 'in:newest,oldest,name_asc,name_desc,products'],
        ]);

        $query = Category::query()->withCount('products');

        if (!empty($validated['q'])) {
            $tokens = preg_split('/\s+/u', trim($validated['q'])) ?: [];

            foreach ($tokens as $token) {
                $query->where(function ($subQuery) use ($token) {
                    $likeToken = '%' . $token . '%';

                    $subQuery->where('name', 'like', $likeToken)
                        ->orWhere('slug', 'like', $likeToken);
                });
            }
        }

        if (!empty($validated['status'])) {
            $query->where('is_active', $validated['status'] === 'active');
        }

        $sort = $validated['sort'] ?? 'name_asc';
        match ($sort) {
            'newest' => $query->latest(),
            'oldest' => $query->oldest(),
            'name_desc' => $query->orderBy('name', 'desc'),
            'products' => $query->orderBy('products_count', 'desc'),
            default => $query->orderBy('name', 'asc'),
        };

        $categories = $query->paginate(15)->appends($request->query());

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'name_translations' => 'nullable|array',
            'name_translations.nl' => 'nullable|string|max:255',
            'name_translations.en' => 'nullable|string|max:255',
            'name_translations.ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['name_translations'] = $this->cleanTranslations($validated['name_translations'] ?? []);

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', __('admin.categories.messages.created'));
    }

    public function edit(string $locale, Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, string $locale, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug,' . $category->id,
            'name_translations' => 'nullable|array',
            'name_translations.nl' => 'nullable|string|max:255',
            'name_translations.en' => 'nullable|string|max:255',
            'name_translations.ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? $category->slug;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['name_translations'] = $this->cleanTranslations($validated['name_translations'] ?? []);

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', __('admin.categories.messages.updated'));
    }

    public function destroy(string $locale, Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', __('admin.categories.messages.delete_has_products'));
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', __('admin.categories.messages.deleted'));
    }

    /**
     * Keep only supported locales with a non-empty name; null when none are left,
     * so the base `name` is used everywhere.
     */
    private function cleanTranslations(array $translations): ?array
    {
        $clean = collect($translations)
            ->only(['nl', 'en', 'ar'])
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->all();

        return $clean ?: null;
    }
}
