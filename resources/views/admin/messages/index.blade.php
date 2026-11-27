@extends('layouts.admin', ['title' => __('admin.contact_messages.title')])

@section('content')
@php($languages = ['nl' => 'Nederlands', 'en' => 'English', 'ar' => 'العربية'])
<div class="w-full max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-primary-dark">{{ __('admin.contact_messages.title') }}</h1>
            <p class="text-sm text-primary-dark/60 mt-1">{{ __('admin.contact_messages.subtitle') }}</p>
        </div>

        <!-- Filter -->
        <div class="inline-flex rounded-lg border border-primary-gray bg-white p-1 text-sm">
            <a href="{{ r('admin.messages.index') }}"
               class="px-3 py-1.5 rounded-md {{ $filter === 'all' ? 'bg-primary-dark text-white' : 'text-primary-dark hover:bg-primary-gray' }}">
                {{ __('admin.contact_messages.filter_all') }}
            </a>
            <a href="{{ r('admin.messages.index', ['filter' => 'unread']) }}"
               class="px-3 py-1.5 rounded-md {{ $filter === 'unread' ? 'bg-primary-dark text-white' : 'text-primary-dark hover:bg-primary-gray' }}">
                {{ __('admin.contact_messages.filter_unread') }}
                @if($unreadCount > 0)
                    <span class="ms-1 inline-flex min-w-5 h-5 px-1.5 items-center justify-center rounded-full bg-amber-500 text-white text-xs font-bold">{{ $unreadCount }}</span>
                @endif
            </a>
        </div>
    </div>

    <!-- List -->
    @forelse($messages as $message)
        <article class="bg-white rounded-xl shadow-lg p-6 space-y-4 {{ $message->read_at ? '' : 'border-s-4 border-amber-500' }}">
            <header class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold text-primary-dark" dir="auto">
                        {{ $message->subject ?: __('admin.contact_messages.no_subject') }}
                        @unless($message->read_at)
                            <span class="ms-2 align-middle px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-xs font-semibold">{{ __('admin.contact_messages.unread_badge') }}</span>
                        @endunless
                    </h2>
                    <p class="text-sm text-primary-dark/70">
                        <bdi>{{ $message->name }}</bdi> ·
                        <a href="mailto:{{ $message->email }}" class="underline underline-offset-2">{{ $message->email }}</a>
                        @if($message->user_id)
                            · <span class="text-primary-dark/50">{{ __('admin.contact_messages.customer_account') }}</span>
                        @endif
                    </p>
                    <p class="text-xs text-primary-dark/50">
                        {{ $message->created_at->copy()->locale(app()->getLocale())->isoFormat('D MMMM YYYY, HH:mm') }}
                        · {{ $languages[$message->locale] ?? '—' }}
                    </p>
                </div>
            </header>

            <div class="rounded-lg bg-primary-50 p-4 text-sm leading-relaxed text-primary-dark whitespace-pre-line" dir="auto">{{ $message->message }}</div>

            <footer class="flex flex-wrap items-center gap-2">
                <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: ' . ($message->subject ?: 'Rukn')) }}"
                   class="px-4 py-2 rounded-lg bg-primary-dark text-white text-sm font-medium hover:bg-primary-dark/90">
                    {{ __('admin.contact_messages.reply') }}
                </a>

                <form method="POST" action="{{ r('admin.messages.update', ['message' => $message]) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="read" value="{{ $message->read_at ? 0 : 1 }}">
                    <button type="submit" class="px-4 py-2 rounded-lg border border-primary-gray text-sm text-primary-dark hover:bg-primary-gray">
                        {{ $message->read_at ? __('admin.contact_messages.mark_unread') : __('admin.contact_messages.mark_read') }}
                    </button>
                </form>

                <form method="POST" action="{{ r('admin.messages.destroy', ['message' => $message]) }}"
                      onsubmit="return confirm(@js(__('admin.contact_messages.delete_confirm')));" class="ms-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50">
                        {{ __('admin.contact_messages.delete') }}
                    </button>
                </form>
            </footer>
        </article>
    @empty
        <div class="bg-white rounded-xl shadow-lg p-12 text-center text-primary-dark/60">
            {{ $filter === 'unread' ? __('admin.contact_messages.empty_unread') : __('admin.contact_messages.empty') }}
        </div>
    @endforelse

    <div>{{ $messages->links() }}</div>
</div>
@endsection
