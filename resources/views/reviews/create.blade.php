{{-- Review page of one order (private link). One form per product that can still be reviewed. --}}
<x-app-layout :title="__('reviews.form.title')" :noindex="true">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-12 space-y-8">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">{{ __('reviews.form.title') }}</h1>
            <p class="mt-2 text-neutral-600">{{ __('reviews.form.subtitle', ['number' => $order->number]) }}</p>
        </div>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        @if (! $order->canBeReviewed())
            <div class="rounded-2xl bg-white shadow-sm border border-neutral-200 p-6 text-neutral-700">
                {{ __('reviews.form.not_delivered') }}
            </div>
        @elseif ($products->isEmpty() && $submitted->isEmpty())
            <div class="rounded-2xl bg-white shadow-sm border border-neutral-200 p-6 text-neutral-700">
                {{ __('reviews.form.nothing_to_review') }}
            </div>
        @endif

        @foreach ($products as $product)
            @php($formId = 'review-' . $product->id)
            <form method="POST" action="{{ r('reviews.store', ['order' => $order->id, 'token' => $order->access_token]) }}"
                  class="rounded-2xl bg-white shadow-sm border border-neutral-200 p-6 space-y-5"
                  x-data="{ rating: {{ (int) (old('product_id') == $product->id ? old('rating', 0) : 0) }}, hover: 0 }">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="flex items-center gap-4">
                    <img src="{{ $product->primaryImageUrl() }}" alt="" class="h-16 w-16 rounded-xl object-cover border border-neutral-200">
                    <h2 class="text-lg font-semibold text-primary-dark">{{ $product->name }}</h2>
                </div>

                <fieldset>
                    <legend class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('reviews.form.rating') }}</legend>
                    <div class="flex items-center gap-1" dir="ltr" @mouseleave="hover = 0">
                        @for ($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer" @mouseenter="hover = {{ $i }}">
                                <input type="radio" name="rating" value="{{ $i }}" x-model.number="rating" class="sr-only" required
                                       @checked(old('product_id') == $product->id && (int) old('rating') === $i)>
                                <span class="sr-only">{{ trans_choice('reviews.stars', $i, ['count' => $i]) }}</span>
                                <svg aria-hidden="true" class="w-9 h-9 transition-colors" :class="(hover || rating) >= {{ $i }} ? 'text-warning' : 'text-neutral-300'" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                </svg>
                            </label>
                        @endfor
                    </div>
                    @if (old('product_id') == $product->id)
                        @error('rating')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                </fieldset>

                <div>
                    <label for="{{ $formId }}-comment" class="block text-sm font-semibold text-neutral-700 mb-1">{{ __('reviews.form.comment') }}</label>
                    <textarea id="{{ $formId }}-comment" name="comment" rows="4" maxlength="2000" dir="auto"
                              placeholder="{{ __('reviews.form.comment_placeholder') }}"
                              class="w-full rounded-xl border border-neutral-300 px-4 py-3 focus:ring-primary-dark/30 focus:border-primary-dark">{{ old('product_id') == $product->id ? old('comment') : '' }}</textarea>
                    @if (old('product_id') == $product->id)
                        @error('comment')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                </div>

                <div>
                    <label for="{{ $formId }}-name" class="block text-sm font-semibold text-neutral-700 mb-1">{{ __('reviews.form.name') }}</label>
                    <input id="{{ $formId }}-name" name="author_name" required maxlength="60" dir="auto"
                           value="{{ old('product_id') == $product->id ? old('author_name') : $defaultName }}"
                           class="w-full rounded-xl border border-neutral-300 px-4 py-3 focus:ring-primary-dark/30 focus:border-primary-dark">
                    <p class="mt-1 text-xs text-neutral-500">{{ __('reviews.form.name_hint') }}</p>
                    @if (old('product_id') == $product->id)
                        @error('author_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('product_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <button type="submit" class="btn-primary" :disabled="rating === 0">{{ __('reviews.form.submit') }}</button>
                    <p class="text-xs text-neutral-500">{{ __('reviews.form.moderation_note') }}</p>
                </div>
            </form>
        @endforeach

        @if ($submitted->isNotEmpty())
            <section class="rounded-2xl bg-white shadow-sm border border-neutral-200 p-6">
                <h2 class="text-lg font-semibold text-primary-dark mb-4">{{ __('reviews.form.your_reviews') }}</h2>
                <ul class="divide-y divide-neutral-200">
                    @foreach ($submitted as $review)
                        <li class="py-3 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-medium text-primary-dark">{{ $review->product?->name ?? __('email.order.unavailable_product') }}</p>
                                <p class="text-sm text-warning" aria-label="{{ trans_choice('reviews.stars', $review->rating, ['count' => $review->rating]) }}" dir="ltr">{{ str_repeat('★', $review->rating) }}<span class="text-neutral-300">{{ str_repeat('★', 5 - $review->rating) }}</span></p>
                            </div>
                            <span class="text-xs rounded-full px-3 py-1 {{ $review->status === 'approved' ? 'bg-green-100 text-green-800' : ($review->status === 'rejected' ? 'bg-neutral-100 text-neutral-600' : 'bg-amber-100 text-amber-800') }}">
                                {{ __('reviews.status.' . $review->status) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-app-layout>
