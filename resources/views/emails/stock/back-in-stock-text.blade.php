{{-- Plain-text part: raw output on purpose (no HTML entities in a text email). --}}
{!! __('email.back_in_stock.title') !!}

{!! __('email.back_in_stock.intro') !!}

{!! $product->name !!}@if ($label) ({!! $label !!})@endif — {!! money($price) !!}

{!! __('email.back_in_stock.button') !!}: {!! $productUrl !!}

{!! __('email.back_in_stock.note') !!}

{!! __('email.back_in_stock.why') !!}

{!! __('email.order.thanks') !!}
