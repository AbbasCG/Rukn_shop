{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
@php
    $paymentLabel = __('checkout.form.payment_options.' . $order->payment_method);
    if ($paymentLabel === 'checkout.form.payment_options.' . $order->payment_method) {
        $paymentLabel = $order->payment_method;
    }
@endphp
{!! __('email.order.greeting', ['name' => $order->name]) !!}

{!! __('email.order.intro') !!}

{!! __('email.order.order_number') !!}: #{!! $order->number !!}
{!! __('email.order.order_date') !!}: {!! $order->created_at->copy()->locale(app()->getLocale())->isoFormat('D MMMM YYYY') !!}

{!! __('email.order.items') !!}
@foreach ($order->items as $item)
- {!! $item->full_name !!} — {!! $item->quantity !!} × {!! money($item->price) !!} = {!! money($item->price * $item->quantity) !!}
@endforeach

{!! __('email.order.subtotal') !!}: {!! money($order->subtotal) !!}
{!! __('email.order.shipping') !!}: {!! (float) $order->shipping_cost > 0 ? money($order->shipping_cost) : __('email.order.free') !!}
{!! __('email.order.total') !!}: {!! money($order->total) !!}

{!! __('email.order.payment') !!}: {!! $paymentLabel !!}
@if ($order->payment_method === 'cod')
{!! __('email.order.payment_cod_note', ['total' => money($order->total)]) !!}
@endif

{!! __('email.order.shipping_address') !!}:
{!! $order->name !!}
{!! $order->address_line1 !!}
@if ($order->address_line2)
{!! $order->address_line2 !!}
@endif
{!! $order->postal_code !!} {!! $order->city !!}
{!! $order->country !!}

{!! policy_text('email.order.delivery') !!}

{!! __('email.order.view_order') !!}: {!! $orderUrl !!}

{!! policy_text('email.order.returns') !!}
{!! __('email.order.questions', ['email' => config('shop.contact_email')]) !!}

{!! __('email.order.thanks') !!}
