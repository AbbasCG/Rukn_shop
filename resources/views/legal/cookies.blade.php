@php
    // Update this date whenever the cookies the shop uses change.
    $updated = \Carbon\Carbon::parse('2026-11-28')->locale(app()->getLocale())->isoFormat('D MMMM YYYY');
    $cookies = [
        ['name' => config('session.cookie'), 'purpose' => __('legal.cookies.session_purpose'),
         'duration' => __('legal.cookies.session_duration', ['minutes' => config('session.lifetime')])],
        ['name' => 'XSRF-TOKEN', 'purpose' => __('legal.cookies.xsrf_purpose'), 'duration' => __('legal.cookies.xsrf_duration')],
        ['name' => 'remember_web_…', 'purpose' => __('legal.cookies.remember_purpose'), 'duration' => __('legal.cookies.remember_duration')],
        ['name' => 'wishlist', 'purpose' => __('legal.cookies.wishlist_purpose'), 'duration' => __('legal.cookies.wishlist_duration')],
    ];
@endphp
<x-app-layout :title="__('legal.cookies.title')" :description="__('legal.cookies.intro')">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-8">
        <section class="rounded-3xl bg-gradient-to-br from-primary-light to-white p-10 text-start">
            <h1 class="text-4xl font-bold text-primary-dark">{{ __('legal.cookies.title') }}</h1>
            <p class="mt-2 text-sm text-primary-dark/70">{{ __('legal.cookies.updated', ['date' => $updated]) }}</p>
        </section>

        <section class="bg-white rounded-3xl shadow-xl border border-primary-gray/50 p-8 space-y-8 text-start">
            <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.cookies.intro') }}</p>

            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-4">{{ __('legal.cookies.table_title') }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-primary-dark/70 border-b border-primary-gray">
                                <th class="py-2 pe-4 text-start font-medium">{{ __('legal.cookies.name') }}</th>
                                <th class="py-2 pe-4 text-start font-medium">{{ __('legal.cookies.purpose') }}</th>
                                <th class="py-2 text-start font-medium">{{ __('legal.cookies.duration') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cookies as $cookie)
                                <tr class="border-b border-primary-gray/50 align-top">
                                    <td class="py-3 pe-4 font-mono text-xs text-primary-dark" dir="ltr">{{ $cookie['name'] }}</td>
                                    <td class="py-3 pe-4 text-primary-dark/80">{{ $cookie['purpose'] }}</td>
                                    <td class="py-3 text-primary-dark/80">{{ $cookie['duration'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.cookies.storage_title') }}</h2>
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.cookies.storage_text') }}</p>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.cookies.third_title') }}</h2>
                <ul class="list-disc ps-5 space-y-2 text-primary-dark/80 leading-relaxed">
                    <li>{{ __('legal.cookies.third_fonts') }}</li>
                    @if (\App\Support\Payments::onlineEnabled())
                        <li>{{ __('legal.cookies.third_mollie') }}</li>
                    @endif
                    <li>{{ __('legal.cookies.third_links') }}</li>
                </ul>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-primary-dark mb-3">{{ __('legal.cookies.manage_title') }}</h2>
                <p class="text-primary-dark/80 leading-relaxed">{{ __('legal.cookies.manage_text') }}</p>
            </div>

            <p class="text-sm text-primary-dark/70">{{ __('legal.cookies.contact', ['email' => config('shop.contact_email')]) }}</p>
        </section>
    </div>
</x-app-layout>
