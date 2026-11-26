@php
    // Same visual language as the order emails; tables + inline styles only.
    $dir = $isRtl ? 'rtl' : 'ltr';
    $start = $isRtl ? 'right' : 'left';
    $font = $isRtl
        ? "Tahoma, 'Segoe UI', Arial, sans-serif"
        : "-apple-system, 'Segoe UI', Helvetica, Arial, sans-serif";
    $dark = '#1F1D20';
    $muted = '#6B5F4E';
    $bg = '#F9F6F0';
    $perks = [
        ['title' => __('email.welcome.perk_orders_title'), 'text' => __('email.welcome.perk_orders'), 'url' => $ordersUrl],
        ['title' => __('email.welcome.perk_checkout_title'), 'text' => __('email.welcome.perk_checkout'), 'url' => $profileUrl],
        ['title' => __('email.welcome.perk_shipping_title'), 'text' => policy_text('email.welcome.perk_shipping'), 'url' => null],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('email.welcome.subject', ['name' => $user->name]) }}</title>
</head>
<body style="margin:0; padding:0; background:{{ $bg }}; font-family:{{ $font }}; color:{{ $dark }};">
    {{-- Preview text shown next to the subject in the inbox --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ __('email.welcome.preheader') }}</div>

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
                            <p style="margin:0 0 12px; font-size:20px; font-weight:bold;">{{ __('email.welcome.greeting', ['name' => $user->name]) }}</p>
                            <p style="margin:0; font-size:15px; line-height:1.7; color:{{ $muted }};">{{ __('email.welcome.intro') }}</p>
                        </td>
                    </tr>

                    {{-- What the account gives you --}}
                    <tr>
                        <td style="padding:20px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $bg }}; border-radius:12px;" dir="{{ $dir }}">
                                @foreach ($perks as $perk)
                                    <tr>
                                        <td style="padding:16px 20px {{ $loop->last ? '16px' : '0' }}; text-align:{{ $start }}; font-size:14px; line-height:1.6;">
                                            <p style="margin:0 0 2px; font-weight:bold; color:{{ $dark }};">
                                                @if ($perk['url'])
                                                    <a href="{{ $perk['url'] }}" style="color:{{ $dark }}; text-decoration:underline;">{{ $perk['title'] }}</a>
                                                @else
                                                    {{ $perk['title'] }}
                                                @endif
                                            </p>
                                            <p style="margin:0; color:{{ $muted }};">{{ $perk['text'] }}</p>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- Button --}}
                    <tr>
                        <td style="padding:28px 32px 0; text-align:{{ $start }};">
                            <a href="{{ $shopUrl }}" style="display:inline-block; background:{{ $dark }}; color:#FFFFFF; text-decoration:none; font-size:15px; font-weight:bold; padding:14px 28px; border-radius:10px;">{{ __('email.welcome.action') }}</a>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:32px; text-align:{{ $start }}; font-size:13px; line-height:1.6; color:{{ $muted }};">
                            <p style="margin:0 0 8px;">{{ __('email.welcome.questions', ['email' => config('shop.contact_email')]) }}</p>
                            <p style="margin:0 0 8px;">{{ __('email.welcome.not_you') }}</p>
                            <p style="margin:0; color:{{ $dark }};">{{ __('email.welcome.thanks') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
