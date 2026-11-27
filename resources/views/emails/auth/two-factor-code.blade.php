@php
    // Same visual language as the other shop emails; tables + inline styles only.
    $dir = $isRtl ? 'rtl' : 'ltr';
    $start = $isRtl ? 'right' : 'left';
    $font = $isRtl
        ? "Tahoma, 'Segoe UI', Arial, sans-serif"
        : "-apple-system, 'Segoe UI', Helvetica, Arial, sans-serif";
    $dark = '#1F1D20';
    $muted = '#6B5F4E';
    $bg = '#F9F6F0';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('two_factor.email.subject', ['code' => $code]) }}</title>
</head>
<body style="margin:0; padding:0; background:{{ $bg }}; font-family:{{ $font }}; color:{{ $dark }};">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ __('two_factor.email.preheader', ['minutes' => $minutes]) }}</div>

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
                            <p style="margin:0; font-size:15px; line-height:1.6; color:{{ $muted }};">{{ __('two_factor.email.intro') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px 0; text-align:center;">
                            <p dir="ltr" style="margin:0; display:inline-block; background:{{ $bg }}; border-radius:12px; padding:16px 28px; font-size:32px; font-weight:bold; letter-spacing:8px; font-family:'Courier New', monospace; color:{{ $dark }};">{{ $code }}</p>
                            <p style="margin:16px 0 0; font-size:14px; line-height:1.6; color:{{ $muted }};">{{ __('two_factor.email.expires', ['minutes' => $minutes]) }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px; text-align:{{ $start }}; font-size:13px; line-height:1.6; color:{{ $muted }};">
                            <p style="margin:0 0 8px;">{{ __('two_factor.email.not_you') }}</p>
                            <p style="margin:0 0 8px;">{{ __('email.order.questions', ['email' => config('shop.contact_email')]) }}</p>
                            <p style="margin:0; color:{{ $dark }};">{{ __('email.welcome.thanks') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
