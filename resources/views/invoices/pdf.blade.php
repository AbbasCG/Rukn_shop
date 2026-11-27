@php
    // Rendered by DomPDF: plain HTML/CSS 2.1, tables for layout, DejaVu Sans
    // (built into DomPDF) for the euro sign and accents.
    $rate = $order->vat_rate !== null ? (float) $order->vat_rate : \App\Support\Vat::rate();
    $vat = $order->vat_amount !== null ? (float) $order->vat_amount : \App\Support\Vat::included((float) $order->total);
    $rateLabel = \App\Support\Vat::rateLabel($rate);
    $date = fn ($value) => $value ? $value->copy()->locale(app()->getLocale())->isoFormat('D MMMM YYYY') : '—';
    $eur = fn ($amount) => '€ ' . number_format((float) $amount, 2, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('invoices.title') }} {{ $order->invoice_number }}</title>
    <style>
        @page { margin: 36px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1F1D20; }
        h1 { font-size: 22px; margin: 0; }
        .muted { color: #6B5F4E; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 1px 0; }
        .lines th { text-align: left; font-size: 9px; text-transform: uppercase; color: #6B5F4E; border-bottom: 1px solid #1F1D20; padding: 6px 4px; }
        .lines td { padding: 7px 4px; border-bottom: 1px solid #EDE8DD; vertical-align: top; }
        .num, .lines th.num { text-align: right; white-space: nowrap; }
        .totals td { padding: 3px 4px; }
        .totals .grand td { font-size: 13px; font-weight: bold; border-top: 1px solid #1F1D20; padding-top: 6px; }
        .box { background: #F9F6F0; padding: 10px 12px; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <h1>{{ $business['name'] }}</h1>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <div style="font-size: 18px; font-weight: bold;">{{ __('invoices.title') }}</div>
                <table class="meta" style="margin-top: 6px;">
                    <tr><td class="muted">{{ __('invoices.number') }}</td><td class="num"><strong>{{ $order->invoice_number }}</strong></td></tr>
                    <tr><td class="muted">{{ __('invoices.date') }}</td><td class="num">{{ $date($order->invoiced_at) }}</td></tr>
                    <tr><td class="muted">{{ __('invoices.order') }}</td><td class="num">#{{ $order->number }}</td></tr>
                    <tr><td class="muted">{{ __('invoices.order_date') }}</td><td class="num">{{ $date($order->created_at) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="margin-top: 24px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 12px;">
                <div class="muted" style="margin-bottom: 4px;">{{ __('invoices.from') }}</div>
                <strong>{{ $business['name'] }}</strong><br>
                @if (filled($business['address']))
                    {{ $business['address'] }}<br>
                @endif
                {{ config('shop.contact_email') }}<br>
                @if (filled($business['kvk']))
                    {{ __('invoices.kvk') }}: {{ $business['kvk'] }}<br>
                @endif
                @if (filled($business['vat_id'] ?? null))
                    {{ __('invoices.vat_id') }}: {{ $business['vat_id'] }}<br>
                @endif
                @if (filled($business['iban'] ?? null))
                    {{ __('invoices.iban') }}: {{ $business['iban'] }}
                @endif
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div class="muted" style="margin-bottom: 4px;">{{ __('invoices.to') }}</div>
                <strong>{{ $order->name }}</strong><br>
                {{ $order->address_line1 }}<br>
                @if ($order->address_line2){{ $order->address_line2 }}<br>@endif
                {{ $order->postal_code }} {{ $order->city }}<br>
                {{ $order->country }}<br>
                {{ $order->email }}
            </td>
        </tr>
    </table>

    <table class="lines" style="margin-top: 24px;">
        <thead>
            <tr>
                <th>{{ __('invoices.description') }}</th>
                <th class="num">{{ __('invoices.quantity') }}</th>
                <th class="num">{{ __('invoices.unit_price') }}</th>
                <th class="num">{{ __('invoices.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->full_name }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">{{ $eur($item->price) }}</td>
                    <td class="num">{{ $eur($item->price * $item->quantity) }}</td>
                </tr>
            @endforeach
            <tr>
                <td>{{ __('invoices.shipping') }}@if ($order->coupon_code) ({{ __('invoices.coupon', ['code' => $order->coupon_code]) }})@endif</td>
                <td class="num">1</td>
                <td class="num">{{ (float) $order->shipping_cost > 0 ? $eur($order->shipping_cost) : __('invoices.free') }}</td>
                <td class="num">{{ $eur($order->shipping_cost) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top: 12px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                <table class="totals">
                    <tr><td>{{ __('invoices.total_excl') }}</td><td class="num">{{ $eur((float) $order->total - $vat) }}</td></tr>
                    <tr><td>{{ __('invoices.vat', ['rate' => $rateLabel]) }}</td><td class="num">{{ $eur($vat) }}</td></tr>
                    <tr class="grand"><td>{{ __('invoices.total_incl') }}</td><td class="num">{{ $eur($order->total) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="box" style="margin-top: 24px;">
        <strong>{{ __('invoices.payment') }}:</strong>
        @if ($order->payment_status === 'paid')
            {{ __('invoices.paid_on', ['date' => $date($order->paid_at)]) }}
        @elseif ($order->payment_method === 'cod')
            {{ __('invoices.pay_on_delivery') }}
        @else
            {{ __('invoices.open') }}
        @endif
        <br>
        <span class="muted">{{ __('invoices.prices_incl', ['rate' => $rateLabel]) }}</span>
    </div>

    <p class="muted" style="margin-top: 24px;">{{ __('invoices.thanks', ['name' => $business['name'], 'email' => config('shop.contact_email')]) }}</p>
</body>
</html>
