@extends('layouts.admin', ['title' => __('size_guide.admin.title')])

@section('content')
@php
    $initial = old('rows', $rows->map(fn ($r) => ['size' => $r->size, 'height_min' => $r->height_min, 'height_max' => $r->height_max])->all());
@endphp
<div class="w-full max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-dark">{{ __('size_guide.admin.title') }}</h1>
        <p class="text-sm text-primary-dark/60 mt-1">{{ __('size_guide.admin.subtitle') }}</p>
    </div>

    <form method="POST" action="{{ r('admin.size-guide.update') }}" class="bg-white rounded-xl shadow p-6 space-y-4"
          x-data="{ rows: @js(array_values($initial)) }">
        @csrf
        @method('PUT')

        <table class="w-full text-sm">
            <thead class="text-primary-dark/70">
                <tr>
                    <th class="py-2 pe-3 text-start font-medium">{{ __('size_guide.admin.size') }}</th>
                    <th class="py-2 pe-3 text-start font-medium">{{ __('size_guide.length_cm') }}</th>
                    <th class="py-2 pe-3 text-start font-medium">{{ __('size_guide.admin.height_min') }}</th>
                    <th class="py-2 pe-3 text-start font-medium">{{ __('size_guide.admin.height_max') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(row, i) in rows" :key="i">
                    <tr class="border-t border-primary-gray">
                        <td class="py-2 pe-3"><input type="text" :name="`rows[${i}][size]`" x-model="row.size" required maxlength="10" dir="ltr" class="w-20 rounded-lg border border-gray-300 px-2 py-1.5 text-center"></td>
                        <td class="py-2 pe-3 text-primary-dark/60" dir="ltr" x-text="isNaN(parseFloat(row.size)) ? '—' : Math.round(parseFloat(row.size) * 2.54) + ' cm'"></td>
                        <td class="py-2 pe-3"><input type="number" :name="`rows[${i}][height_min]`" x-model="row.height_min" required min="100" max="230" class="w-24 rounded-lg border border-gray-300 px-2 py-1.5 text-center"></td>
                        <td class="py-2 pe-3"><input type="number" :name="`rows[${i}][height_max]`" x-model="row.height_max" required min="100" max="230" class="w-24 rounded-lg border border-gray-300 px-2 py-1.5 text-center"></td>
                        <td class="py-2 text-end"><button type="button" @click="rows.splice(i, 1)" class="rounded-lg px-2 py-1 text-red-600 hover:bg-red-50">{{ __('size_guide.admin.remove') }}</button></td>
                    </tr>
                </template>
            </tbody>
        </table>
        <button type="button" @click="rows.push({ size: '', height_min: '', height_max: '' })" class="rounded-lg border border-primary-gray px-4 py-2 text-sm text-primary-dark hover:bg-primary-gray">+ {{ __('size_guide.admin.add') }}</button>

        @if ($errors->any())
            <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</p>
        @endif

        <p class="text-xs text-primary-dark/60">{{ __('size_guide.admin.hint') }}</p>
        <button type="submit" class="rounded-lg bg-primary-dark px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-dark/90">{{ __('size_guide.admin.save') }}</button>
    </form>
</div>
@endsection
