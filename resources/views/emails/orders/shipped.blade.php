@php
    // Same visual language as the confirmation email; tables + inline styles only.
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
    $carrier = $order->carrierName();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __($trackingUpdate ? 'email.shipped.subject_update' : 'email.shipped.subject', ['number' => $order->number]) }}</title>
</head>
<body style="margin:0; padding:0; background:{{ $bg }}; font-family:{{ $font }}; color:{{ $dark }};">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ __('email.shipped.preheader') }}</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $bg }};" dir="{{ $dir }}">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#FFFFFF; border-radius:16px; overflow:hidden;" dir="{{ $dir }}">

                    {{-- Header --}}
                    <tr>
                        <td style="background:{{ $dark }}; padding:24px 32px; text-align:{{ $start }};">
                            <span style="font-size:24px; font-weight:bold; color:#FDFBF8;">{{ $isRtl ? 'ركن' : 'Rukn' }}</span>
                        </td>
                    </tr>

                    {{-- Intro --}}
                    <tr>
                        <td style="padding:32px 32px 8px; text-align:{{ $start }};">
                            <p style="margin:0 0 12px; font-size:18px; font-weight:bold;">{{ __('email.order.greeting', ['name' => $order->name]) }}</p>
                            <p style="margin:0; font-size:15px; line-height:1.6; color:{{ $muted }};">
                                {{ __($trackingUpdate ? 'email.shipped.intro_update' : 'email.shipped.intro', ['number' => $order->number]) }}
                            </p>
                        </td>
                    </tr>

                    {{-- Track & trace --}}
                    <tr>
                        <td style="padding:20px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $bg }}; border-radius:12px;" dir="{{ $dir }}">
                                <tr>
                                    <td style="padding:20px; text-align:{{ $start }}; font-size:14px; line-height:1.6;">
                                        @if ($order->hasTracking())
                                            <p style="margin:0 0 4px; color:{{ $muted }};">{{ $carrier ? __('email.shipped.carrier_number', ['carrier' => $carrier]) : __('email.shipped.tracking_number') }}</p>
                                            <p style="margin:0 0 16px; font-size:18px; font-weight:bold; letter-spacing:0.5px;" dir="ltr">{{ $order->tracking_number }}</p>
                                            @if ($trackingUrl)
                                                <a href="{{ $trackingUrl }}" style="display:inline-block; background:{{ $dark }}; color:#FFFFFF; text-decoration:none; font-size:15px; font-weight:bold; padding:14px 28px; border-radius:10px;">{{ __('email.shipped.track') }}</a>
                                            @endif
                                        @else
                                            <p style="margin:0; color:{{ $muted }};">{{ __('email.shipped.no_tracking') }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @include('emails.orders.partials.summary', ['itemsTitle' => __('email.shipped.items')])

                    {{-- Address + delivery --}}
                    <tr>
                        <td style="padding:24px 32px 0; text-align:{{ $start }}; font-size:14px; line-height:1.6;">
                            <p style="margin:0 0 4px; font-weight:bold;">{{ __('email.order.shipping_address') }}</p>
                            <p style="margin:0 0 16px; color:{{ $muted }};" dir="auto">
                                {{ $order->name }}<br>
                                {{ $order->address_line1 }}<br>
                                @if ($order->address_line2){{ $order->address_line2 }}<br>@endif
                                {{ $order->postal_code }} {{ $order->city }}<br>
                                {{ $order->country }}
                            </p>
                            <p style="margin:0 0 16px; color:{{ $muted }};">{{ policy_text('email.order.delivery') }}</p>
                            @if ($order->payment_method === 'cod' && $order->payment_status !== 'paid')
                                <p style="margin:0 0 16px; color:{{ $dark }}; font-weight:bold;">{{ __('email.order.payment_cod_note', ['total' => money($order->total)]) }}</p>
                            @endif
                            <a href="{{ $orderUrl }}" style="color:{{ $dark }};">{{ __('email.order.view_order') }}</a>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:32px; text-align:{{ $start }}; font-size:13px; line-height:1.6; color:{{ $muted }};">
                            <p style="margin:0 0 8px;">{{ policy_text('email.order.returns') }}</p>
                            <p style="margin:0 0 8px;">{{ __('email.order.questions', ['email' => config('shop.contact_email')]) }}</p>
                            <p style="margin:0; color:{{ $dark }};">{{ __('email.order.thanks') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
