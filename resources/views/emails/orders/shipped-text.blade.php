{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
{!! __('email.order.greeting', ['name' => $order->name]) !!}

{!! __($trackingUpdate ? 'email.shipped.intro_update' : 'email.shipped.intro', ['number' => $order->number]) !!}

@if ($order->hasTracking())
{!! $order->carrierName() ? __('email.shipped.carrier_number', ['carrier' => $order->carrierName()]) : __('email.shipped.tracking_number') !!}: {!! $order->tracking_number !!}
@if ($trackingUrl)
{!! __('email.shipped.track') !!}: {!! $trackingUrl !!}
@endif
@else
{!! __('email.shipped.no_tracking') !!}
@endif

{!! __('email.shipped.items') !!}
@foreach ($order->items as $item)
- {!! $item->full_name !!} × {!! $item->quantity !!}
@endforeach

{!! __('email.order.shipping_address') !!}:
{!! $order->name !!}
{!! $order->address_line1 !!}
@if ($order->address_line2)
{!! $order->address_line2 !!}
@endif
{!! $order->postal_code !!} {!! $order->city !!}
{!! $order->country !!}

{!! policy_text('email.order.delivery') !!}
@if ($order->payment_method === 'cod' && $order->payment_status !== 'paid')
{!! __('email.order.payment_cod_note', ['total' => money($order->total)]) !!}
@endif

{!! __('email.order.view_order') !!}: {!! $orderUrl !!}

{!! policy_text('email.order.returns') !!}
{!! __('email.order.questions', ['email' => config('shop.contact_email')]) !!}

{!! __('email.order.thanks') !!}
