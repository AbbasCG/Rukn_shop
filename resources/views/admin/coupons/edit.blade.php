@extends('layouts.admin', ['title' => __('coupons.admin.title')])

@section('content')
<div class="w-full max-w-5xl mx-auto space-y-6">
    <a href="{{ r('admin.coupons.index') }}" class="text-sm text-primary-dark underline underline-offset-2">← {{ __('coupons.admin.title') }}</a>
    <form method="POST" action="{{ r('admin.coupons.update', $coupon) }}" class="bg-white rounded-xl shadow p-6">
        @csrf @method('PUT')
        <h1 class="text-xl font-semibold text-primary-dark mb-4" dir="ltr">{{ $coupon->code }}</h1>
        @include('admin.coupons._form', ['coupon' => $coupon])
        <p class="mt-4 text-xs text-primary-dark/50">{{ __('coupons.admin.uses') }}: {{ $coupon->uses_count }}</p>
        <div class="mt-5">
            <button type="submit" class="rounded-lg bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark/90">{{ __('coupons.admin.save') }}</button>
        </div>
    </form>
</div>
@endsection
