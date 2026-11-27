@php
    $dir = $isRtl ? 'rtl' : 'ltr';
    $start = $isRtl ? 'right' : 'left';
    $font = $isRtl ? "Tahoma, 'Segoe UI', Arial, sans-serif" : "-apple-system, 'Segoe UI', Helvetica, Arial, sans-serif";
    $languages = ['nl' => 'Nederlands', 'en' => 'English', 'ar' => 'العربية'];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('email.contact.title') }}</title>
</head>
<body style="margin:0; padding:0; background:#F9F6F0; font-family:{{ $font }}; color:#1F1D20;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F9F6F0;" dir="{{ $dir }}">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#FFFFFF; border-radius:16px; overflow:hidden;" dir="{{ $dir }}">
                    <tr>
                        <td style="background:#1F1D20; padding:24px 32px; text-align:{{ $start }}; color:#FDFBF8;">
                            <span style="font-size:20px; font-weight:bold;">{{ __('email.contact.title') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px 8px; text-align:{{ $start }}; font-size:14px; line-height:1.7;">
                            <strong><bdi>{{ $contactMessage->name }}</bdi></strong><br>
                            <a href="mailto:{{ $contactMessage->email }}" style="color:#1F1D20;">{{ $contactMessage->email }}</a><br>
                            <span style="color:#6B5F4E;">
                                {{ __('email.new_order.language') }}: {{ $languages[$contactMessage->locale] ?? '—' }}
                                · {{ $contactMessage->created_at->copy()->locale(app()->getLocale())->isoFormat('D MMMM YYYY, HH:mm') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px 0; text-align:{{ $start }};">
                            <p style="margin:0 0 8px; font-size:16px; font-weight:bold;" dir="auto">{{ $contactMessage->subject ?: __('email.contact.no_subject') }}</p>
                            <div style="background:#F9F6F0; border-radius:12px; padding:16px; font-size:15px; line-height:1.7; white-space:pre-line;" dir="auto">{{ $contactMessage->message }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px 32px; text-align:{{ $start }};">
                            <a href="{{ $adminUrl }}" style="display:inline-block; background:#1F1D20; color:#FFFFFF; text-decoration:none; font-size:15px; font-weight:bold; padding:14px 28px; border-radius:10px;">{{ __('email.contact.open_in_admin') }}</a>
                            <p style="margin:16px 0 0; font-size:13px; color:#6B5F4E;">{{ __('email.contact.reply_hint') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
