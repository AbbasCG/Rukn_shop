@php
    // Update this date whenever the policy text or the services used change.
    $updated = \Carbon\Carbon::parse('2026-11-26')->locale(app()->getLocale())->isoFormat('D MMMM YYYY');
    $business = config('shop.business');
    $email = config('shop.contact_email');
    $online = \App\Support\Payments::onlineEnabled();
    $r2 = config('shop.media_disk') === 'r2';
    $p = fn (string $key, array $replace = []) => __('legal.privacy.' . $key, $replace + ['name' => $business['name'], 'email' => $email]);

    $collected = array_values(array_filter([
        ['title' => $p('what_order_title'), 'text' => $p('what_order')],
        ['title' => $p('what_account_title'), 'text' => $p('what_account')],
        ['title' => $p('what_contact_title'), 'text' => $p('what_contact')],
        ['title' => $p('what_payment_title'), 'text' => $online ? $p('what_payment_online') : $p('what_payment_cod')],
        ['title' => $p('what_technical_title'), 'text' => $p('what_technical')],
    ]));

    $recipients = array_values(array_filter([
        ['title' => $p('share_hosting_title'), 'text' => $p('share_hosting')],
        ['title' => $p('share_email_title'), 'text' => $p('share_email')],
        $online ? ['title' => 'Mollie', 'text' => $p('share_mollie')] : null,
        $r2 ? ['title' => 'Cloudflare', 'text' => $p('share_cloudflare')] : null,
        ['title' => $p('share_carrier_title'), 'text' => $p('share_carrier')],
        ['title' => $p('share_links_title'), 'text' => $p('share_links')],
    ]));
@endphp
<x-app-layout :title="__('seo.privacy_title')" :description="__('seo.privacy_description')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
        <section class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-10 text-start">
            <h1 class="text-4xl font-bold text-primary-dark">{{ __('legal.privacy.title') }}</h1>
            <p class="mt-2 text-sm text-primary-dark/70">{{ __('legal.privacy.last_updated') }}: {{ $updated }}</p>
        </section>

        <section class="bg-white rounded-3xl shadow-xl border border-primary-gray/50 p-8 space-y-8 text-start text-primary-dark/80 leading-relaxed">
            <div class="space-y-3">
                <p>{{ $p('intro') }}</p>
                @if (app()->getLocale() !== 'nl')
                    <p class="text-sm text-primary-dark/60">{{ $p('translation_note') }}</p>
                @endif
            </div>

            {{-- 1. Who is responsible --}}
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ $p('who_title') }}</h2>
                <p>{{ $p('who_text') }}</p>
                <div class="mt-3 rounded-xl bg-primary-light/60 p-4 text-sm space-y-1">
                    <p class="font-semibold text-primary-dark"><bdi>{{ $business['name'] }}</bdi></p>
                    @if ($business['address'])
                        <p><bdi>{{ $business['address'] }}</bdi></p>
                    @endif
                    @if ($business['kvk'])
                        <p>{{ __('legal.withdrawal.kvk') }}: <span dir="ltr">{{ $business['kvk'] }}</span></p>
                    @endif
                    <p><a href="mailto:{{ $email }}" class="underline" dir="ltr">{{ $email }}</a></p>
                </div>
            </div>

            {{-- 2. What we collect --}}
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ $p('what_title') }}</h2>
                <dl class="space-y-4">
                    @foreach ($collected as $item)
                        <div>
                            <dt class="font-semibold text-primary-dark">{{ $item['title'] }}</dt>
                            <dd>{{ $item['text'] }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-4">
                    {{ $p('what_cookies') }}
                    <a href="{{ r('cookies') }}" class="underline text-primary-dark">{{ __('legal.cookies.title') }}</a>.
                </p>
            </div>

            {{-- 3. Why and on what legal basis --}}
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ $p('why_title') }}</h2>
                <ul class="list-disc ps-6 space-y-2">
                    <li>{{ $p('why_contract') }}</li>
                    <li>{{ $p('why_legal') }}</li>
                    <li>{{ $p('why_interest') }}</li>
                </ul>
                <p class="mt-4">{{ $p('why_not') }}</p>
            </div>

            {{-- 4. Who receives data --}}
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ $p('share_title') }}</h2>
                <p class="mb-4">{{ $p('share_intro') }}</p>
                <dl class="space-y-4">
                    @foreach ($recipients as $item)
                        <div>
                            <dt class="font-semibold text-primary-dark">{{ $item['title'] }}</dt>
                            <dd>{{ $item['text'] }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-4">{{ $p('transfer') }}</p>
            </div>

            {{-- 5. How long --}}
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ $p('keep_title') }}</h2>
                <ul class="list-disc ps-6 space-y-2">
                    <li>{{ $p('keep_orders') }}</li>
                    <li>{{ $p('keep_account') }}</li>
                    <li>{{ $p('keep_contact') }}</li>
                    <li>{{ $p('keep_logs') }}</li>
                </ul>
            </div>

            {{-- 6. Security --}}
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ $p('security_title') }}</h2>
                <p>{{ $p('security') }}</p>
            </div>

            {{-- 7. Your rights --}}
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ $p('rights_title') }}</h2>
                <p>{{ $p('rights') }}</p>
                <p class="mt-3">{{ $p('rights_how') }}</p>
                <p class="mt-3">{{ $p('rights_account') }}</p>
                <p class="mt-3">
                    {{ $p('complaint') }}
                    <a href="https://www.autoriteitpersoonsgegevens.nl" target="_blank" rel="noopener noreferrer" class="underline text-primary-dark" dir="ltr">autoriteitpersoonsgegevens.nl</a>.
                </p>
            </div>

            {{-- 8. Changes --}}
            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ $p('changes_title') }}</h2>
                <p>{{ $p('changes') }}</p>
            </div>
        </section>

        <div class="text-center">
            <a href="{{ r('home') }}" class="inline-flex items-center gap-2 text-primary-dark hover:text-primary-dark/70 transition-colors duration-300">
                <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>{{ __('Back to Home') }}</span>
            </a>
        </div>
    </div>
</x-app-layout>
