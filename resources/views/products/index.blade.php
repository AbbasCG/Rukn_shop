<x-app-layout>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
        <!-- Soft background accent -->
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute inset-6 rounded-3xl bg-gradient-to-br from-primary-gray/40 via-white to-emerald-50 blur-2xl"></div>
        </div>

        <!-- Top: Title + meta -->
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 mb-2">Fresh picks</div>
                <h1 class="text-4xl font-bold text-primary-dark" style="font-family: 'Signika', sans-serif;">Shop</h1>
                <p class="mt-1 text-sm text-neutral-600">Discover our curated collection from Rukn Shop.</p>
            </div>
            <div class="flex items-center gap-3 text-sm text-neutral-600">
                <div class="px-3 py-2 rounded-xl bg-white/80 border border-primary-gray/60 shadow-sm backdrop-blur">{{ $products->total() }} products</div>
                <div class="px-3 py-2 rounded-xl bg-white/80 border border-primary-gray/60 shadow-sm backdrop-blur">Updated {{ now()->format('M d') }}</div>
            </div>
        </div>

        <!-- Filters & Search Bar Card -->
        <form x-data="{ pr: '{{ request('price_range') }}', min: '{{ request('min_price') }}', max: '{{ request('max_price') }}' }" method="GET" action="{{ route('products.index') }}" class="bg-white/85 backdrop-blur rounded-2xl shadow-sm border border-primary-gray/60 p-5 flex flex-col md:flex-row md:items-end md:justify-between gap-4 ring-1 ring-white">
            <!-- Left: Search -->
            <div class="w-full md:max-w-md">
                <label for="q" class="sr-only">Search products</label>
                <div class="relative">
                    <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Search products..." class="w-full rounded-xl border border-neutral-300 pl-10 pr-3 py-3 text-sm focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition bg-white/80" />
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Right: Filters Row -->
            <div class="w-full md:flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <!-- Category -->
                <div>
                    <label for="category" class="block text-xs font-semibold text-neutral-600 mb-1">Category</label>
                    <select id="category" name="category" class="w-full rounded-xl border border-neutral-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition bg-white/80">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Price Range -->
                <div>
                    <label for="price_range" class="block text-xs font-semibold text-neutral-600 mb-1">Price range (€)</label>
                    <select id="price_range" name="price_range" x-model="pr" @change="
                        if (pr === '') { min = ''; max = '' }
                        else if (pr === '0-25') { min = '0'; max = '25' }
                        else if (pr === '25-50') { min = '25'; max = '50' }
                        else if (pr === '50-100') { min = '50'; max = '100' }
                        else if (pr === '100+') { min = '100'; max = '' }
                    " class="w-full rounded-xl border border-neutral-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition bg-white/80">
                        <option value="">All prices</option>
                        <option value="0-25">Under €25</option>
                        <option value="25-50">€25 – €50</option>
                        <option value="50-100">€50 – €100</option>
                        <option value="100+">Over €100</option>
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <label for="sort" class="block text-xs font-semibold text-neutral-600 mb-1">Sort</label>
                    <select id="sort" name="sort" class="w-full rounded-xl border border-neutral-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-dark/20 focus:border-primary-dark transition bg-white/80">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High</option>
                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Popular</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                    </select>
                </div>
            </div>

            <!-- Apply Button -->
            <div class="w-full md:w-auto">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-primary-dark to-emerald-600 text-white text-sm font-semibold shadow-sm hover:shadow-md transition">
                    Apply
                </button>
            </div>

            <!-- Hidden min/max fields populated from price_range -->
            <input type="hidden" name="min_price" :value="min">
            <input type="hidden" name="max_price" :value="max">
        </form>

        <!-- Result count -->
        <div class="flex items-center justify-between">
            <p class="text-sm text-neutral-600">
                Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
            </p>
        </div>

        <!-- Products Grid / Empty State -->
        @if($products->count() === 0)
            <div class="bg-white rounded-2xl shadow-sm border border-primary-gray/60 p-10 text-center">
                <div class="mx-auto w-16 h-16 rounded-full bg-primary-gray/60 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-primary-dark">No products found</h3>
                <p class="mt-1 text-sm text-neutral-600">Try different filters or search terms.</p>
                <a href="{{ route('products.index') }}" class="mt-4 inline-flex items-center px-4 py-2 rounded-lg border border-primary-gray text-primary-dark hover:bg-primary-gray transition">Back to all products</a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 lg:gap-8">
                @foreach($products as $product)
                    @php
                        $thumb = optional($product->images->first())->path;
                        $imageUrl = $thumb ? asset('storage/' . $thumb) : asset('images/placeholder.jpg');
                    @endphp
                    <div class="group flex flex-col bg-white/90 backdrop-blur rounded-2xl shadow-sm overflow-hidden border border-primary-gray/60 hover:shadow-lg hover:-translate-y-1.5 hover:border-primary-dark/40 transition-all duration-200">
                        <a href="{{ route('products.show', $product) }}" class="block">
                            <div class="relative aspect-[4/5] bg-primary-gray/40 overflow-hidden">
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                @if(($product->stock ?? 0) > 0 && ($product->stock ?? 0) <= 5)
                                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 border border-primary-gray text-xs font-semibold text-primary-dark">Low stock</span>
                                @elseif(($product->stock ?? 0) <= 0)
                                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold">Sold out</span>
                                @endif
                                <span class="absolute bottom-3 left-3 px-2.5 py-1 rounded-full bg-black/70 text-white text-[11px] tracking-wide uppercase">{{ $product->category->name ?? 'Shop' }}</span>
                            </div>
                        </a>

                        <div class="p-4 space-y-2">
                            <h3 class="text-sm font-semibold text-primary-dark line-clamp-2">
                                <a href="{{ route('products.show', $product) }}" class="hover:underline decoration-2 decoration-emerald-500">{{ $product->name }}</a>
                            </h3>
                            @if(!empty($product->short_description))
                                <p class="text-xs text-neutral-600 line-clamp-2">{{ $product->short_description }}</p>
                            @endif
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-lg font-bold text-primary-dark">€{{ number_format($product->price, 2, ',', '.') }}</span>
                                @if(($product->stock ?? 0) <= 0)
                                    <span class="text-xs text-neutral-500">Out of stock</span>
                                @endif
                            </div>
                            <div class="pt-2 flex items-center gap-2">
                                <div x-data="{ added:false }" class="relative flex-1">
                                    <button type="button"
                                        @click.prevent="
                                            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                                            fetch('{{ route('cart.store') }}', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                                                body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
                                            }).then(r => r.json()).then(d => { if (d && d.ok) { $store.cart.count = d.count; added = true; setTimeout(() => added = false, 1500); } });
                                        "
                                        class="w-full inline-flex items-center justify-center px-3 py-2.5 text-xs font-semibold rounded-xl bg-gradient-to-r from-primary-dark to-emerald-600 text-white transform transition duration-300 active:scale-105 hover:shadow-md disabled:bg-neutral-300 disabled:text-neutral-600 disabled:cursor-not-allowed" @if(($product->stock ?? 0) <= 0) disabled @endif>
                                        <span class="inline-flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4"/></svg>
                                            Add to cart
                                        </span>
                                    </button>
                                    <div x-cloak x-show="added" x-transition class="absolute -top-2 right-0 rounded-xl shadow-lg bg-primary-dark text-white px-3 py-1 text-xs">Added to cart!</div>
                                </div>
                                <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center px-3 py-2.5 text-xs font-semibold rounded-xl border border-neutral-300 text-neutral-700 hover:bg-neutral-100 transition">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</x-app-layout>