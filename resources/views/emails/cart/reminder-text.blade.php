{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
{!! __('email.order.greeting', ['name' => $user->name]) !!}

{!! __($second ? 'email.cart_reminder.intro_second' : 'email.cart_reminder.intro') !!}

@foreach ($lines as $cartLine)
- {!! $cartLine->product->name !!}@if ($cartLine->variant) ({!! $cartLine->variant->label() !!})@endif — {!! (int) $cartLine->quantity !!} × {!! money($cartLine->unitPrice()) !!}
@endforeach

{!! __('email.cart_reminder.note') !!}

{!! __('email.cart_reminder.button') !!}: {!! $cartUrl !!}

{!! __('email.cart_reminder.why') !!} {!! __('email.cart_reminder.unsubscribe') !!}: {!! $unsubscribeUrl !!}

{!! __('email.order.thanks') !!}
