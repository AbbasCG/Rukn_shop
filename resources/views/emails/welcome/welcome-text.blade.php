{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
{!! __('email.welcome.greeting', ['name' => $user->name]) !!}

{!! __('email.welcome.intro') !!}

- {!! __('email.welcome.perk_orders_title') !!}: {!! __('email.welcome.perk_orders') !!}
  {!! $ordersUrl !!}
- {!! __('email.welcome.perk_checkout_title') !!}: {!! __('email.welcome.perk_checkout') !!}
  {!! $profileUrl !!}
- {!! __('email.welcome.perk_shipping_title') !!}: {!! policy_text('email.welcome.perk_shipping') !!}

{!! __('email.welcome.action') !!}: {!! $shopUrl !!}

{!! __('email.welcome.questions', ['email' => config('shop.contact_email')]) !!}
{!! __('email.welcome.not_you') !!}

{!! __('email.welcome.thanks') !!}
