@props(['status' => null, 'orderStatus' => null])
@php
    $normalizedStatus = strtolower(trim($status ?? ''));
    $normalizedOrder = strtolower(trim($orderStatus ?? ''));

    // Infer paid when order is already moving forward but payment status is missing
    $paidishOrders = ['paid', 'processing', 'shipped', 'delivered', 'complete', 'completed'];
    if ($normalizedStatus === '' && in_array($normalizedOrder, $paidishOrders, true)) {
        $normalizedStatus = 'paid';
    }
    // Default empty status to pending
    if ($normalizedStatus === '') {
        $normalizedStatus = 'pending';
    }

    $paymentLabels = [
        'paid' => ['label' => 'Paid', 'class' => 'bg-emerald-50 text-emerald-800 border border-emerald-200'],
        'open' => ['label' => 'Pending', 'class' => 'bg-amber-50 text-amber-800 border border-amber-200'],
        'pending' => ['label' => 'Pending', 'class' => 'bg-amber-50 text-amber-800 border border-amber-200'],
        'failed' => ['label' => 'Failed', 'class' => 'bg-rose-50 text-rose-800 border border-rose-200'],
        'expired' => ['label' => 'Expired', 'class' => 'bg-neutral-100 text-neutral-700 border border-neutral-200'],
        'canceled' => ['label' => 'Canceled', 'class' => 'bg-neutral-100 text-neutral-700 border border-neutral-200'],
        'cancelled' => ['label' => 'Canceled', 'class' => 'bg-neutral-100 text-neutral-700 border border-neutral-200'],
        'refunded' => ['label' => 'Refunded', 'class' => 'bg-blue-50 text-blue-800 border border-blue-200'],
    ];

    $paymentInfo = $paymentLabels[$normalizedStatus] ?? [
        'label' => ucfirst($normalizedStatus),
        'class' => 'bg-neutral-100 text-neutral-700 border border-neutral-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold']) }}>
    <span class="{{ $paymentInfo['class'] }} px-0 py-0 rounded-full w-full inline-flex justify-center items-center">{{ $paymentInfo['label'] }}</span>
</span>
