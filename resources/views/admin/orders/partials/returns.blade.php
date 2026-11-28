{{-- Returns & credit notes for this order (admin). --}}
@php
    $returns = $order->returns()->with('items.orderItem')->get();
    $left = \App\Support\CreditNotes::returnableItems($order);
    $canReturn = \App\Support\CreditNotes::canReturn($order);
    $shippingLeft = \App\Support\CreditNotes::refundableShipping($order);
    $paidOnline = $order->payment_method === 'mollie';
@endphp
<section id="returns" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-6 scroll-mt-24">
    <h2 class="text-lg font-semibold text-primary-dark">{{ __('returns.title') }}</h2>

    @if ($returns->isEmpty())
        <p class="text-sm text-primary-dark/60">{{ __('returns.none') }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-primary-dark/70">
                        <th class="text-start py-2 font-semibold">{{ __('returns.number') }}</th>
                        <th class="text-start py-2 font-semibold">{{ __('admin.orders.show.sections.order_items') }}</th>
                        <th class="text-start py-2 font-semibold">{{ __('returns.amount') }}</th>
                        <th class="text-start py-2 font-semibold">{{ __('returns.status') }}</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($returns as $return)
                        <tr class="align-top">
                            <td class="py-3 font-semibold text-primary-dark">
                                {{ $return->credit_note_number }}
                                <span class="block text-xs font-normal text-primary-dark/60">{{ $return->created_at->translatedFormat('j M Y') }}</span>
                            </td>
                            <td class="py-3 text-primary-dark/80">
                                @foreach ($return->items as $line)
                                    <span class="block">{{ $line->quantity }} × <bdi>{{ $line->orderItem?->full_name }}</bdi></span>
                                @endforeach
                                @if ((float) $return->shipping_amount > 0)
                                    <span class="block">{{ __('returns.shipping') }}</span>
                                @endif
                                @if ($return->restocked && $return->items->isNotEmpty())
                                    <span class="block text-xs text-emerald-700">↺ {{ __('returns.restocked') }}</span>
                                @endif
                                @if ($return->reason)
                                    <span class="block text-xs text-primary-dark/60">“{{ $return->reason }}”</span>
                                @endif
                            </td>
                            <td class="py-3 font-semibold text-primary-dark">{{ money($return->total) }}</td>
                            <td class="py-3">
                                @if ($return->isRefunded())
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">✓ {{ __('returns.refunded_on', ['date' => $return->refunded_at->translatedFormat('j M Y')]) }}</span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">{{ __('returns.not_refunded') }}</span>
                                    <p class="mt-2 text-xs text-primary-dark/70">{{ __($paidOnline ? 'returns.how_to_refund_online' : 'returns.how_to_refund_bank', ['amount' => money($return->total)]) }}</p>
                                    <form method="POST" action="{{ r('admin.returns.refunded', ['return' => $return->id]) }}" class="mt-2"
                                          onsubmit="return confirm(@js(__('returns.mark_refunded_confirm', ['amount' => money($return->total)])))">
                                        @csrf
                                        <input type="hidden" name="notify" value="1">
                                        <button type="submit" class="rounded-lg bg-primary-dark px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-dark/90">{{ __('returns.mark_refunded') }}</button>
                                    </form>
                                @endif
                            </td>
                            <td class="py-3 text-end">
                                <a href="{{ r('admin.returns.credit-note', ['return' => $return->id]) }}" class="text-sm font-semibold text-primary-dark underline underline-offset-2">{{ __('returns.download') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($canReturn)
        <form method="POST" action="{{ r('admin.orders.returns.store', $order) }}" class="rounded-xl border border-primary-gray p-5 space-y-4"
              onsubmit="return confirm(@js(__('returns.confirm')))" x-data="{ refunded: false }">
            @csrf
            <div>
                <h3 class="font-semibold text-primary-dark">{{ __('returns.new') }}</h3>
                <p class="mt-1 text-xs text-primary-dark/60">{{ __('returns.help', ['invoice' => $order->invoice_number ?? '—']) }}</p>
            </div>

            <div class="space-y-2">
                @foreach ($order->orderItems as $item)
                    @php $max = (int) $left->get($item->id, 0); @endphp
                    <div class="flex flex-wrap items-center justify-between gap-3 text-sm {{ $max === 0 ? 'opacity-50' : '' }}">
                        <span class="text-primary-dark"><bdi>{{ $item->full_name }}</bdi> · {{ money($item->price) }}</span>
                        <label class="flex items-center gap-2 text-primary-dark/70">
                            {{ __('returns.returned_qty') }}
                            <input type="number" name="quantities[{{ $item->id }}]" min="0" max="{{ $max }}" value="{{ old('quantities.' . $item->id, 0) }}" @disabled($max === 0)
                                   class="w-20 rounded-lg border border-gray-300 px-2 py-1 text-center">
                            {{ __('returns.of', ['count' => $max]) }}
                        </label>
                    </div>
                @endforeach
            </div>

            @if ($shippingLeft > 0)
                <label class="flex items-start gap-2 text-sm text-primary-dark">
                    <input type="checkbox" name="refund_shipping" value="1" class="mt-1" @checked(old('refund_shipping'))>
                    <span>{{ __('returns.refund_shipping', ['amount' => money($shippingLeft)]) }}<span class="block text-xs text-primary-dark/60">{{ __('returns.refund_shipping_hint') }}</span></span>
                </label>
            @endif
            <label class="flex items-start gap-2 text-sm text-primary-dark">
                <input type="checkbox" name="restock" value="1" class="mt-1" @checked(old('restock', true))>
                <span>{{ __('returns.restock') }}<span class="block text-xs text-primary-dark/60">{{ __('returns.restock_hint') }}</span></span>
            </label>
            <label class="flex items-start gap-2 text-sm text-primary-dark">
                <input type="checkbox" name="refunded" value="1" class="mt-1" x-model="refunded" @checked(old('refunded'))>
                <span>{{ __('returns.refunded_now') }}</span>
            </label>
            <label class="flex items-start gap-2 text-sm text-primary-dark ms-6" x-show="refunded" x-cloak>
                <input type="checkbox" name="notify" value="1" class="mt-1" checked>
                <span>{{ __('returns.notify') }}</span>
            </label>
            <label class="block text-sm text-primary-dark">
                {{ __('returns.reason') }}
                <input type="text" name="reason" maxlength="500" value="{{ old('reason') }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </label>

            @if ($errors->return->any())
                <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->return->first() }}</p>
            @endif

            <button type="submit" class="rounded-lg bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark/90">{{ __('returns.submit') }}</button>
        </form>
    @elseif ($returns->isNotEmpty() && $left->isEmpty())
        <p class="text-sm text-primary-dark/60">{{ __('returns.all_returned') }}</p>
    @endif
</section>
