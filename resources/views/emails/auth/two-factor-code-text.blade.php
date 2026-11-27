{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
{!! __('email.order.greeting', ['name' => $user->name]) !!}

{!! __('two_factor.email.intro') !!}

{!! $code !!}

{!! __('two_factor.email.expires', ['minutes' => $minutes]) !!}

{!! __('two_factor.email.not_you') !!}

{!! __('email.welcome.thanks') !!}
