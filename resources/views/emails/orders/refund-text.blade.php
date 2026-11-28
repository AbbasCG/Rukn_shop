{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
{!! __('email.order.greeting', ['name' => $order->name]) !!}

{!! __('email.refund.intro', ['number' => $order->number, 'amount' => money($return->total), 'method' => $method]) !!}

@foreach ($return->items as $returned)
- {!! $returned->quantity !!} × {!! $returned->orderItem->full_name !!}: {!! money($returned->price * $returned->quantity) !!}
@endforeach
@if ((float) $return->shipping_amount > 0)
- {!! __('email.refund.shipping') !!}: {!! money($return->shipping_amount) !!}
@endif
{!! __('email.refund.total') !!}: {!! money($return->total) !!}

{!! __('email.refund.timing') !!}
{!! __('email.refund.attached', ['number' => $return->credit_note_number]) !!}

{!! __('email.refund.button') !!}: {!! $orderUrl !!}

{!! __('email.order.questions', ['email' => config('shop.contact_email')]) !!}
{!! __('email.order.thanks') !!}
