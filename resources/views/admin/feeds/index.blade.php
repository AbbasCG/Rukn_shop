@extends('layouts.admin', ['title' => __('feeds.admin.title')])

@section('content')
@php
    $feed = \App\Support\ProductFeed::products();
    $languages = ['nl' => 'Nederlands', 'en' => 'English', 'ar' => 'العربية'];
@endphp
<div class="w-full max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-dark">{{ __('feeds.admin.title') }}</h1>
        <p class="text-sm text-primary-dark/60 mt-1">{{ __('feeds.admin.subtitle') }}</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6 space-y-4">
        <h2 class="text-lg font-semibold text-primary-dark">{{ __('feeds.admin.links') }}</h2>
        @foreach ($languages as $code => $language)
            @php $url = route('feeds.products', ['locale' => $code]); @endphp
            <div class="flex flex-wrap items-center gap-3" x-data="{ copied: false }">
                <span class="w-24 text-sm font-medium text-primary-dark">{{ $language }}</span>
                <input type="text" readonly value="{{ $url }}" dir="ltr" class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm" @focus="$el.select()">
                <button type="button" class="rounded-lg border border-primary-gray px-3 py-2 text-sm text-primary-dark hover:bg-primary-gray"
                        @click="navigator.clipboard.writeText(@js($url)); copied = true; setTimeout(() => copied = false, 1500)">
                    <span x-show="!copied">{{ __('feeds.admin.copy') }}</span><span x-show="copied" x-cloak>✓</span>
                </button>
                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-sm text-primary-dark underline underline-offset-2">{{ __('feeds.admin.open') }}</a>
            </div>
        @endforeach
        <p class="text-sm text-primary-dark/70">{{ __('feeds.admin.count', ['count' => $feed['included']->count()]) }}</p>
        @if ($feed['without_photo']->isNotEmpty())
            <p class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-900">
                {{ __('feeds.admin.without_photo', ['count' => $feed['without_photo']->count()]) }}
                {{ $feed['without_photo']->pluck('name')->take(10)->implode(', ') }}
            </p>
        @endif
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="bg-white rounded-xl shadow p-6 space-y-3">
            <h2 class="text-lg font-semibold text-primary-dark">Google Merchant Center</h2>
            <ol class="list-decimal ps-5 space-y-1.5 text-sm text-primary-dark/80">
                @foreach (__('feeds.admin.google_steps') as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ol>
        </div>
        <div class="bg-white rounded-xl shadow p-6 space-y-3">
            <h2 class="text-lg font-semibold text-primary-dark">Instagram / Facebook (Meta)</h2>
            <ol class="list-decimal ps-5 space-y-1.5 text-sm text-primary-dark/80">
                @foreach (__('feeds.admin.meta_steps') as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ol>
        </div>
    </div>

    <p class="text-xs text-primary-dark/60">{{ __('feeds.admin.notes') }}</p>
</div>
@endsection
