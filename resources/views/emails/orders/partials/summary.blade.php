{{-- Items and totals rows, shared by the customer and staff order emails.
     Optional $itemsTitle. Expects the parent's $order, $dir, $start, $end, $dark, $muted and $line. --}}
                    {{-- Items --}}
                    <tr>
                        <td style="padding:8px 32px 0; text-align:{{ $start }};">
                            <p style="margin:0 0 8px; font-size:16px; font-weight:bold;">{{ $itemsTitle ?? __('email.order.items') }}</p>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" dir="{{ $dir }}">
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td style="padding:12px 0; border-bottom:1px solid {{ $line }}; text-align:{{ $start }}; font-size:14px;">
                                            <bdi>{{ $item->display_name }}</bdi><br>
                                            @if ($item->variant_label)<span style="font-size:13px; color:{{ $muted }};"><bdi>{{ $item->variant_label }}</bdi></span><br>@endif
                                            <span style="font-size:13px; color:{{ $muted }};">{{ __('email.order.quantity') }}: {{ $item->quantity }} × {{ money($item->price) }}</span>
                                        </td>
                                        <td style="padding:12px 0; border-bottom:1px solid {{ $line }}; text-align:{{ $end }}; font-size:14px; white-space:nowrap; vertical-align:top;">
                                            {{ money($item->price * $item->quantity) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    {{-- Totals --}}
                    <tr>
                        <td style="padding:12px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" dir="{{ $dir }}" style="font-size:14px;">
                                <tr>
                                    <td style="padding:4px 0; text-align:{{ $start }}; color:{{ $muted }};">{{ __('email.order.subtotal') }}</td>
                                    <td style="padding:4px 0; text-align:{{ $end }};">{{ money($order->subtotal) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0; text-align:{{ $start }}; color:{{ $muted }};">{{ __('email.order.shipping') }}</td>
                                    <td style="padding:4px 0; text-align:{{ $end }};">{{ (float) $order->shipping_cost > 0 ? money($order->shipping_cost) : __('email.order.free') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0 0; text-align:{{ $start }}; font-size:16px; font-weight:bold;">{{ __('email.order.total') }}</td>
                                    <td style="padding:10px 0 0; text-align:{{ $end }}; font-size:16px; font-weight:bold;">{{ money($order->total) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

