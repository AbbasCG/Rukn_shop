@extends('layouts.admin', ['title' => __('reviews.admin.title')])

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">{{ __('reviews.admin.title') }}</h1>
            <p class="text-sm text-primary-dark/60 mt-1">{{ __('reviews.admin.subtitle') }}</p>
        </div>

        <nav class="inline-flex rounded-lg border border-primary-gray bg-white p-1 text-sm" aria-label="{{ __('reviews.admin.title') }}">
            @foreach (\App\Models\Review::STATUSES as $option)
                <a href="{{ r('admin.reviews.index', ['status' => $option]) }}"
                   @if ($status === $option) aria-current="page" @endif
                   class="px-3 py-1.5 rounded-md {{ $status === $option ? 'bg-primary-dark text-white' : 'text-primary-dark hover:bg-primary-gray' }}">
                    {{ __('reviews.admin.filters.' . $option) }}
                    <span class="ms-1 opacity-70">{{ $counts[$option] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    @forelse ($reviews as $review)
        <article class="bg-white rounded-xl shadow-lg p-6 space-y-4 {{ $review->status === 'pending' ? 'border-s-4 border-amber-500' : '' }}">
            <header class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold text-primary-dark">
                        @if ($review->product)
                            <a href="{{ r('products.show', $review->product) }}" target="_blank" class="underline underline-offset-2">{{ $review->product->name }}</a>
                        @else
                            {{ __('email.order.unavailable_product') }}
                        @endif
                    </h2>
                    <p class="text-sm text-primary-dark/70">
                        <span class="text-warning text-base" dir="ltr" aria-label="{{ trans_choice('reviews.stars', $review->rating, ['count' => $review->rating]) }}">{{ str_repeat('★', $review->rating) }}<span class="text-neutral-300">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        · <bdi>{{ $review->author_name }}</bdi>
                        @if ($review->order)
                            · <a href="{{ r('admin.orders.show', $review->order) }}" class="underline underline-offset-2">{{ __('reviews.admin.order') }} #{{ $review->order->number }}</a>
                        @endif
                    </p>
                    <p class="text-xs text-primary-dark/50">{{ $review->created_at->copy()->locale(app()->getLocale())->isoFormat('D MMMM YYYY, HH:mm') }}</p>
                </div>
            </header>

            <div class="rounded-lg bg-primary-50 p-4 text-sm leading-relaxed text-primary-dark whitespace-pre-line" dir="auto">{{ $review->comment ?: __('reviews.admin.no_comment') }}</div>

            <footer class="flex flex-wrap items-center gap-2">
                @if ($review->status !== 'approved')
                    <form method="POST" action="{{ r('admin.reviews.update', $review) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-primary-dark text-white text-sm font-medium hover:bg-primary-dark/90">{{ __('reviews.admin.approve') }}</button>
                    </form>
                @endif
                @if ($review->status !== 'rejected')
                    <form method="POST" action="{{ r('admin.reviews.update', $review) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="px-4 py-2 rounded-lg border border-primary-gray text-primary-dark text-sm font-medium hover:bg-primary-gray">{{ __('reviews.admin.reject') }}</button>
                    </form>
                @endif
                <form method="POST" action="{{ r('admin.reviews.destroy', $review) }}" onsubmit="return confirm(@js(__('reviews.admin.delete_confirm')))">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-lg text-red-700 text-sm font-medium hover:bg-red-50">{{ __('reviews.admin.delete') }}</button>
                </form>
            </footer>
        </article>
    @empty
        <div class="bg-white rounded-xl shadow p-10 text-center text-primary-dark/60">{{ __('reviews.admin.empty') }}</div>
    @endforelse

    {{ $reviews->links() }}
</div>
@endsection
