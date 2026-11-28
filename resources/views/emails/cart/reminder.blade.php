@php
    // Same visual language as the other shop emails; tables + inline styles only.
    $dir = $isRtl ? 'rtl' : 'ltr';
    $start = $isRtl ? 'right' : 'left';
    $font = $isRtl
        ? "Tahoma, 'Segoe UI', Arial, sans-serif"
        : "-apple-system, 'Segoe UI', Helvetica, Arial, sans-serif";
    $dark = '#1F1D20';
    $muted = '#6B5F4E';
    $line = '#EDE8DD';
    $bg = '#F9F6F0';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('email.cart_reminder.subject') }}</title>
</head>
<body style="margin:0; padding:0; background:{{ $bg }}; font-family:{{ $font }}; color:{{ $dark }};">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ __('email.cart_reminder.preheader') }}</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ $bg }};" dir="{{ $dir }}">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#FFFFFF; border-radius:16px; overflow:hidden;" dir="{{ $dir }}">
                    <tr>
                        <td style="background:{{ $dark }}; padding:24px 32px; text-align:{{ $start }};">
                            <span style="font-size:24px; font-weight:bold; color:#FDFBF8;">{{ $isRtl ? 'ركن' : 'Rukn' }}</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 32px 8px; text-align:{{ $start }};">
                            <p style="margin:0 0 12px; font-size:18px; font-weight:bold;">{{ __('email.order.greeting', ['name' => $user->name]) }}</p>
                            <p style="margin:0; font-size:15px; line-height:1.6; color:{{ $muted }};">{{ __($second ? 'email.cart_reminder.intro_second' : 'email.cart_reminder.intro') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" dir="{{ $dir }}">
                                @foreach ($lines as $cartLine)
                                    @php $product = $cartLine->product; $images = $product->imageUrls(); @endphp
                                    <tr>
                                        <td width="72" style="padding:8px 0; border-bottom:1px solid {{ $line }};">
                                            @if ($images)
                                                <img src="{{ $images[0] }}" width="56" height="70" alt="" style="display:block; width:56px; height:70px; object-fit:cover; border-radius:8px; background:{{ $bg }};">
                                            @endif
                                        </td>
                                        <td style="padding:8px 0; border-bottom:1px solid {{ $line }}; text-align:{{ $start }}; font-size:14px;">
                                            <span dir="auto" style="font-weight:bold;">{{ $product->name }}</span>
                                            @if ($cartLine->variant)
                                                <br><span dir="auto" style="color:{{ $muted }};">{{ $cartLine->variant->label() }}</span>
                                            @endif
                                            <br><span style="color:{{ $muted }};">{{ (int) $cartLine->quantity }} × {{ money($cartLine->unitPrice()) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                            <p style="margin:12px 0 0; font-size:13px; line-height:1.6; color:{{ $muted }};">{{ __('email.cart_reminder.note') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px 0; text-align:{{ $start }};">
                            <a href="{{ $cartUrl }}" style="display:inline-block; background:{{ $dark }}; color:#FFFFFF; text-decoration:none; font-size:15px; font-weight:bold; padding:14px 28px; border-radius:10px;">{{ __('email.cart_reminder.button') }}</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px; text-align:{{ $start }}; font-size:13px; line-height:1.6; color:{{ $muted }};">
                            <p style="margin:0 0 8px;">{{ __('email.cart_reminder.why') }} <a href="{{ $unsubscribeUrl }}" style="color:{{ $muted }};">{{ __('email.cart_reminder.unsubscribe') }}</a></p>
                            <p style="margin:0; color:{{ $dark }};">{{ __('email.order.thanks') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
