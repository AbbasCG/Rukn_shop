{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
@php
    $paymentLabel = __('checkout.form.payment_options.' . $order->payment_method);
    if ($paymentLabel === 'checkout.form.payment_options.' . $order->payment_method) {
        $paymentLabel = $order->payment_method;
    }
    $languages = ['nl' => 'Nederlands', 'en' => 'English', 'ar' => 'العربية'];
@endphp
{!! __('email.new_order.intro') !!}
#{!! $order->number !!} · {!! money($order->total) !!}
@if ($lowStock->isNotEmpty())

{!! __('email.new_order.low_stock_title') !!}
@foreach ($lowStock as $product)
- {!! $product['stock'] > 0
    ? __('email.new_order.low_stock_line', ['product' => $product['name'], 'count' => $product['stock']])
    : __('email.new_order.out_of_stock_line', ['product' => $product['name']]) !!}
@endforeach
@endif

{!! __('email.new_order.customer') !!}: {!! $order->name !!}
{!! __('email.new_order.email') !!}: {!! $order->email !!}
{!! __('email.new_order.phone') !!}: {!! $order->phone !!}
{!! __('email.new_order.language') !!}: {!! $languages[$order->locale] ?? $order->locale ?? '—' !!}

{!! __('email.new_order.items') !!}
@foreach ($order->items as $item)
- {!! $item->full_name !!} — {!! $item->quantity !!} × {!! money($item->price) !!} = {!! money($item->price * $item->quantity) !!}
@endforeach

{!! __('email.order.subtotal') !!}: {!! money($order->subtotal) !!}
{!! __('email.order.shipping') !!}: {!! (float) $order->shipping_cost > 0 ? money($order->shipping_cost) : __('email.order.free') !!}
{!! __('email.order.total') !!}: {!! money($order->total) !!}

{!! __('email.order.payment') !!}: {!! $paymentLabel !!}
@if ($order->payment_method === 'cod')
{!! __('email.new_order.payment_cod_note', ['total' => money($order->total)]) !!}
@endif

{!! __('email.order.shipping_address') !!}:
{!! $order->name !!}
{!! $order->address_line1 !!}
@if ($order->address_line2)
{!! $order->address_line2 !!}
@endif
{!! $order->postal_code !!} {!! $order->city !!}
{!! $order->country !!}

{!! __('email.new_order.open_in_admin') !!}: {!! $adminUrl !!}
{!! __('email.new_order.reply_hint') !!}
