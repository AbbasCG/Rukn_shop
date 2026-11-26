{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
@php($languages = ['nl' => 'Nederlands', 'en' => 'English', 'ar' => 'العربية'])
{!! __('email.contact.title') !!}

{!! $contactMessage->name !!} <{!! $contactMessage->email !!}>
{!! __('email.new_order.language') !!}: {!! $languages[$contactMessage->locale] ?? '—' !!}

{!! $contactMessage->subject ?: __('email.contact.no_subject') !!}

{!! $contactMessage->message !!}

{!! __('email.contact.open_in_admin') !!}: {!! $adminUrl !!}
{!! __('email.contact.reply_hint') !!}
