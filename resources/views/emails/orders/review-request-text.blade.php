{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
{!! __('email.order.greeting', ['name' => $order->name]) !!}

{!! __('email.review_request.intro', ['number' => $order->number]) !!}

@foreach ($products as $product)
- {!! $product->name !!}
@endforeach

{!! __('email.review_request.button') !!}: {!! $reviewUrl !!}

{!! __('email.review_request.note') !!}
{!! __('email.review_request.problem', ['email' => config('shop.contact_email')]) !!}

{!! __('email.order.thanks') !!}
