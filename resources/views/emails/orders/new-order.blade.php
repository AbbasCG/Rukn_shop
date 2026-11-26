@php
    // Staff notification. Same visual language as the customer email; tables + inline styles only.
    $dir = $isRtl ? 'rtl' : 'ltr';
    $start = $isRtl ? 'right' : 'left';
    $end = $isRtl ? 'left' : 'right';
    $font = $isRtl
        ? "Tahoma, 'Segoe UI', Arial, sans-serif"
        : "-apple-system, 'Segoe UI', Helvetica, Arial, sans-serif";
    $dark = '#1F1D20';
    $muted = '#6B5F4E';
    $line = '#EDE8DD';
    $bg = '#F9F6F0';
    $paymentLabel = __('checkout.form.payment_options.' . $order->payment_method);
    if ($paymentLabel === 'checkout.form.payment_options.' . $order->payment_method) {
        $paymentLabel = $order->payment_method;
    }
    $languages = ['nl' => 'Nederlands', 'en' => 'English', 'ar' => 'العربية'];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('email.new_order.subject', ['number' => $order->number, 'name' => $order->name, 'total' => money($order->total)]) }}</title>
</head>
<body style="margin:0; padding:0; background:{{ $bg }}; font-family:{{ $font }}; color:{{ $dark }};">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $bg }};" dir="{{ $dir }}">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#FFFFFF; border-radius:16px; overflow:hidden;" dir="{{ $dir }}">

                    {{-- Header: order number + total at a glance --}}
                    <tr>
                        <td style="background:{{ $dark }}; padding:24px 32px; text-align:{{ $start }}; color:#FDFBF8;">
                            <p style="margin:0 0 4px; font-size:14px; opacity:0.8;">{{ __('email.new_order.intro') }}</p>
                            <p style="margin:0; font-size:22px; font-weight:bold;">#{{ $order->number }} · {{ money($order->total) }}</p>
                        </td>
                    </tr>

                    {{-- Low stock warning first: it needs action --}}
                    @if ($lowStock->isNotEmpty())
                        <tr>
                            <td style="padding:24px 32px 0;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FEF3C7; border-radius:12px;" dir="{{ $dir }}">
                                    <tr>
                                        <td style="padding:14px 16px; text-align:{{ $start }}; font-size:14px; line-height:1.6; color:#78350F;">
                                            <strong>{{ __('email.new_order.low_stock_title') }}</strong><br>
                                            @foreach ($lowStock as $product)
                                                <bdi>{{ $product['stock'] > 0
                                                    ? __('email.new_order.low_stock_line', ['product' => $product['name'], 'count' => $product['stock']])
                                                    : __('email.new_order.out_of_stock_line', ['product' => $product['name']]) }}</bdi><br>
                                            @endforeach
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    {{-- Customer --}}
                    <tr>
                        <td style="padding:24px 32px 8px; text-align:{{ $start }}; font-size:14px; line-height:1.7;">
                            <p style="margin:0 0 4px; font-size:16px; font-weight:bold;">{{ __('email.new_order.customer') }}</p>
                            <bdi>{{ $order->name }}</bdi><br>
                            {{ __('email.new_order.email') }}: <a href="mailto:{{ $order->email }}" style="color:{{ $dark }};">{{ $order->email }}</a><br>
                            {{ __('email.new_order.phone') }}: <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->phone) }}" style="color:{{ $dark }};" dir="ltr">{{ $order->phone }}</a><br>
                            {{ __('email.new_order.language') }}: {{ $languages[$order->locale] ?? $order->locale ?? '—' }}
                        </td>
                    </tr>

                    @include('emails.orders.partials.summary', ['itemsTitle' => __('email.new_order.items')])

                    {{-- Payment + address --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" dir="{{ $dir }}">
                                <tr>
                                    <td width="50%" style="padding-{{ $end }}:12px; vertical-align:top; text-align:{{ $start }}; font-size:14px; line-height:1.6;">
                                        <p style="margin:0 0 4px; font-weight:bold;">{{ __('email.order.payment') }}</p>
                                        <p style="margin:0; color:{{ $muted }};">
                                            {{ $paymentLabel }}
                                            @if ($order->payment_method === 'cod')
                                                <br>{{ __('email.new_order.payment_cod_note', ['total' => money($order->total)]) }}
                                            @endif
                                        </p>
                                    </td>
                                    <td width="50%" style="vertical-align:top; text-align:{{ $start }}; font-size:14px; line-height:1.6;">
                                        <p style="margin:0 0 4px; font-weight:bold;">{{ __('email.order.shipping_address') }}</p>
                                        <p style="margin:0; color:{{ $muted }};" dir="auto">
                                            {{ $order->name }}<br>
                                            {{ $order->address_line1 }}<br>
                                            @if ($order->address_line2){{ $order->address_line2 }}<br>@endif
                                            {{ $order->postal_code }} {{ $order->city }}<br>
                                            {{ $order->country }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Action --}}
                    <tr>
                        <td style="padding:28px 32px 32px; text-align:{{ $start }};">
                            <a href="{{ $adminUrl }}" style="display:inline-block; background:{{ $dark }}; color:#FFFFFF; text-decoration:none; font-size:15px; font-weight:bold; padding:14px 28px; border-radius:10px;">{{ __('email.new_order.open_in_admin') }}</a>
                            <p style="margin:16px 0 0; font-size:13px; color:{{ $muted }};">{{ __('email.new_order.reply_hint') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
