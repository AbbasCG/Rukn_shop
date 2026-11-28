<x-app-layout :title="__('cart_reminders.title')" :noindex="true">
    <div class="max-w-lg mx-auto px-4 py-20 text-center">
        <h1 class="text-2xl font-bold text-primary-dark">{{ __('cart_reminders.title') }}</h1>
        @if ($done)
            <p class="mt-4 text-neutral-700">{{ __('cart_reminders.done') }}</p>
            <p class="mt-2 text-sm text-neutral-500">{{ __('cart_reminders.turn_on_again') }}</p>
        @else
            <p class="mt-4 text-neutral-700">{{ __('cart_reminders.question') }}</p>
            <form method="POST" action="{{ $action }}" class="mt-6">
                <button type="submit" class="btn-primary">{{ __('cart_reminders.button') }}</button>
            </form>
        @endif
        <a href="{{ r('home') }}" class="mt-8 inline-block text-sm text-primary-dark underline underline-offset-2">{{ __('cart_reminders.back') }}</a>
    </div>
</x-app-layout>
