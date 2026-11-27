@php
    // Official model form: Directive 2011/83/EU, Annex I(B) (EN) and the annex
    // to art. 6:230o BW (NL); the Arabic text is a translation.
    $updated = \Carbon\Carbon::parse('2026-11-26')->locale(app()->getLocale())->isoFormat('D MMMM YYYY');
    $business = config('shop.business');
    $email = config('shop.contact_email');
    $field = 'w-full border-0 border-b border-dashed border-primary-dark/40 bg-transparent px-0 py-1 focus:ring-0 focus:border-primary-dark';
@endphp
@push('structured-data')
    <style>
        @media print {
            header, nav, footer, [role="alert"], .print-hide { display: none !important; }
            body, .print-plain { background: #fff !important; box-shadow: none !important; }
        }
    </style>
@endpush
<x-app-layout :title="__('legal.withdrawal.title')" :description="__('legal.withdrawal.intro', ['days' => config('shop.returns.days')])">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8"
         x-data="{ name: '', address: '', items: '', order: '', ordered: '', date: '' }">

        <section class="print-hide rounded-3xl bg-gradient-to-br from-primary-light to-white p-10 text-start space-y-3">
            <h1 class="text-4xl font-bold text-primary-dark">{{ __('legal.withdrawal.title') }}</h1>
            <p class="text-sm text-primary-dark/70">{{ __('legal.withdrawal.updated', ['date' => $updated]) }}</p>
            <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.withdrawal.intro', ['days' => config('shop.returns.days')]) }}</p>
        </section>

        <section class="print-hide bg-white rounded-3xl shadow-xl border border-primary-gray/50 p-8 space-y-3 text-start">
            <h2 class="text-2xl font-semibold text-primary-dark">{{ __('legal.withdrawal.how_title') }}</h2>
            <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.withdrawal.how_email', ['email' => $email]) }}</p>
            <p class="text-primary-dark/80 leading-relaxed">
                {{ __('legal.withdrawal.how_return') }}
                <a href="{{ r('terms-of-service') }}" class="font-semibold underline underline-offset-4">{{ __('legal.withdrawal.terms_link') }}</a>
            </p>
            @if (app()->getLocale() === 'ar')
                <p class="text-sm text-primary-dark/60">{{ __('legal.withdrawal.translation_note') }}</p>
            @endif
        </section>

        {{-- The form itself (printable) --}}
        <section class="print-plain bg-white rounded-3xl shadow-xl border border-primary-gray/50 p-8 text-start space-y-5" id="withdrawal-form">
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark">{{ __('legal.withdrawal.form_heading') }}</h2>
                <p class="text-sm text-primary-dark/70">{{ __('legal.withdrawal.form_note') }}</p>
            </div>

            <div class="text-sm leading-relaxed text-primary-dark">
                <p class="font-semibold">{{ __('legal.withdrawal.form_to') }}</p>
                <p><bdi>{{ $business['name'] }}</bdi></p>
                @if ($business['address'])
                    <p><bdi>{{ $business['address'] }}</bdi></p>
                @else
                    <p class="text-primary-dark/60">{{ __('legal.withdrawal.missing_address', ['email' => $email]) }}</p>
                @endif
                <p><bdi>{{ $email }}</bdi></p>
                @if ($business['kvk'])
                    <p>{{ __('legal.withdrawal.kvk') }}: <span dir="ltr">{{ $business['kvk'] }}</span></p>
                @endif
            </div>

            <p class="text-sm leading-relaxed text-primary-dark">{{ __('legal.withdrawal.form_notice') }}</p>
            <textarea x-model="items" rows="2" class="{{ $field }} resize-none" aria-label="{{ __('legal.withdrawal.form_notice') }}"></textarea>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
                <label class="block text-primary-dark">{{ __('legal.withdrawal.form_order_number') }}
                    <input type="text" x-model="order" dir="ltr" class="{{ $field }}"></label>
                <label class="block text-primary-dark">{{ __('legal.withdrawal.form_ordered') }}
                    <input type="text" x-model="ordered" class="{{ $field }}"></label>
                <label class="block text-primary-dark">{{ __('legal.withdrawal.form_name') }}
                    <input type="text" x-model="name" autocomplete="name" class="{{ $field }}"></label>
                <label class="block text-primary-dark">{{ __('legal.withdrawal.form_address') }}
                    <input type="text" x-model="address" autocomplete="street-address" class="{{ $field }}"></label>
                <label class="block text-primary-dark">{{ __('legal.withdrawal.form_signature') }}
                    <span class="block h-10 border-b border-dashed border-primary-dark/40"></span></label>
                <label class="block text-primary-dark">{{ __('legal.withdrawal.form_date') }}
                    <input type="text" x-model="date" class="{{ $field }}"></label>
            </div>

            <p class="text-xs text-primary-dark/60">{{ __('legal.withdrawal.form_delete') }}</p>
        </section>

        <div class="print-hide flex flex-wrap gap-3">
            {{-- Opens the visitor's email app with the filled-in form. --}}
            <a :href="`mailto:{{ $email }}?subject=${encodeURIComponent(@js(__('legal.withdrawal.email_subject')) + order)}&body=${encodeURIComponent([
                    @js(__('legal.withdrawal.form_heading')), '',
                    @js(__('legal.withdrawal.form_notice')), items, '',
                    @js(__('legal.withdrawal.form_order_number')) + ': ' + order,
                    @js(__('legal.withdrawal.form_ordered')) + ': ' + ordered,
                    @js(__('legal.withdrawal.form_name')) + ': ' + name,
                    @js(__('legal.withdrawal.form_address')) + ': ' + address,
                    @js(__('legal.withdrawal.form_date')) + ': ' + date,
                ].join('\n'))}`"
               href="mailto:{{ $email }}"
               class="btn-primary">{{ __('legal.withdrawal.email_button') }}</a>
            <button type="button" onclick="window.print()" class="btn-secondary">{{ __('legal.withdrawal.print') }}</button>
        </div>
    </div>
</x-app-layout>
