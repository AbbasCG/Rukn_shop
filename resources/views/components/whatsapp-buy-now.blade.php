@props(['product', 'quantity' => 1, 'class' => ''])

@if(\App\Helpers\WhatsAppHelper::isEnabled())
    @php
        $whatsappUrl = \App\Helpers\WhatsAppHelper::generateProductLink($product, $quantity);
        $isDisabled = $product->stock <= 0;
    @endphp

    <a 
        href="{{ $whatsappUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        @if($isDisabled)
            onclick="event.preventDefault(); return false;"
            aria-disabled="true"
        @endif
        {{ $attributes->merge([
            'class' => 'flex-1 sm:flex-1 min-w-0 py-3 px-4 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white text-base font-semibold rounded-xl shadow-md hover:shadow-xl transition-all transform hover:-translate-y-0.5 whitespace-nowrap inline-flex items-center justify-center gap-2 no-underline' . 
            ($isDisabled ? ' opacity-50 cursor-not-allowed pointer-events-none' : '')
        ]) }}
        title="{{ __('product.buy_now_whatsapp') }}">
        <!-- WhatsApp Icon -->
        <svg class="w-6 h-6 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.255.949c-1.383.681-2.645 1.563-3.556 2.723-1.907 2.449-2.384 5.945-1.271 9.216.553 1.53 1.425 2.946 2.531 4.09 1.175 1.202 2.55 2.157 4.032 2.783 1.574.675 3.21.932 4.868.675 1.832-.306 3.548-1.249 4.8-2.675 1.383-1.562 2.083-3.61 1.879-5.652-.121-1.283-.469-2.546-1.034-3.692-.565-1.147-1.339-2.174-2.289-2.953-2.129-1.77-5.159-2.528-8.281-2.11z"/>
        </svg>
        <span class="truncate">{{ __('product.buy_now_whatsapp') }}</span>
    </a>
@endif
