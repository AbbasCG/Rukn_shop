{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
{!! __('email.order.greeting', ['name' => $user->name]) !!}

{!! __('email.password_reset.intro') !!}

{!! __('email.password_reset.action') !!}: {!! $resetUrl !!}

{!! __('email.password_reset.expires', ['minutes' => $minutes]) !!}

{!! __('email.password_reset.not_you') !!}
{!! __('email.order.questions', ['email' => config('shop.contact_email')]) !!}

{!! __('email.welcome.thanks') !!}
