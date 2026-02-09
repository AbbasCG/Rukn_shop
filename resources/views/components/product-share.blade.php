@props(['product', 'url' => null, 'variant' => 'default'])

@php
    $shareUrl = $url ?? request()->fullUrl();
    $shareTitle = $product->name;
    $sharePrice = number_format($product->price, 2);
    $shareMessage = __('product.share_message', ['name' => $shareTitle, 'price' => $sharePrice]);
    
    // Encode for URLs
    $encodedUrl = urlencode($shareUrl);
    $encodedMessage = urlencode($shareMessage);
    $encodedTitle = urlencode($shareTitle);
    
    // Social media share URLs
    $whatsappUrl = "https://wa.me/?text=" . urlencode($shareMessage . " " . $shareUrl);
    $facebookUrl = "https://www.facebook.com/sharer/sharer.php?u=" . $encodedUrl;
    $twitterUrl = "https://twitter.com/intent/tweet?text=" . $encodedMessage . "&url=" . $encodedUrl;
    $telegramUrl = "https://t.me/share/url?url=" . $encodedUrl . "&text=" . $encodedMessage;
    
    // Determine button styling based on variant
    $buttonClass = $variant === 'compact' 
        ? 'flex items-center gap-1.5 px-3 py-2 bg-transparent hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 text-sm font-medium rounded-lg transition-all border border-neutral-300 dark:border-neutral-600 hover:border-neutral-400 dark:hover:border-neutral-500'
        : 'flex items-center gap-2 px-4 py-2.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-200 text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md';
    
    $iconSize = $variant === 'compact' ? 'w-4 h-4' : 'w-5 h-5';
@endphp

<!-- Share Button -->
<div class="relative inline-block" x-data="{ open: false }">
    <!-- Main Share Button -->
    <button 
        @click="open = !open"
        class="{{ $buttonClass }}"
        :aria-expanded="open"
        aria-label="{{ __('product.share_product') }}">
        <!-- Share Icon -->
        <svg class="{{ $iconSize }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
        </svg>
        @if($variant !== 'icon-only')
            <span>{{ __('product.share') }}</span>
        @endif
    </button>

    <!-- Share Options Dropdown -->
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute {{ app()->getLocale() === 'ar' ? 'left-0' : 'right-0' }} mt-2 w-64 bg-white dark:bg-neutral-800 rounded-xl shadow-xl border border-neutral-200 dark:border-neutral-700 z-50 overflow-hidden"
        style="display: none;">
        
        <!-- WhatsApp -->
        <a 
            href="{{ $whatsappUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
            <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('product.share_via_whatsapp') }}</div>
            </div>
        </a>

        <!-- Facebook -->
        <a 
            href="{{ $facebookUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('product.share_via_facebook') }}</div>
            </div>
        </a>

        <!-- X (Twitter) -->
        <a 
            href="{{ $twitterUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
            <div class="w-10 h-10 bg-black dark:bg-neutral-900 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('product.share_via_twitter') }}</div>
            </div>
        </a>

        <!-- Telegram -->
        <a 
            href="{{ $telegramUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('product.share_via_telegram') }}</div>
            </div>
        </a>

        <!-- Copy Link -->
        <button 
            onclick="copyToClipboard('{{ $shareUrl }}')"
            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors border-t border-neutral-200 dark:border-neutral-700">
            <div class="w-10 h-10 bg-neutral-600 dark:bg-neutral-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ __('product.copy_link') }}</div>
            </div>
        </button>
    </div>
</div>

<!-- Toast Notification for Copy Success -->
<div id="copyToast" class="fixed bottom-4 {{ app()->getLocale() === 'ar' ? 'left-4' : 'right-4' }} bg-green-600 text-white px-6 py-3 rounded-xl shadow-xl z-50 transform translate-y-20 opacity-0 transition-all duration-300">
    <div class="flex items-center gap-2">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="font-semibold">{{ __('product.link_copied') }}</span>
    </div>
</div>

<script>
    // Copy to clipboard function
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                showCopyToast();
            }).catch(err => {
                console.error('Failed to copy:', err);
                fallbackCopyToClipboard(text);
            });
        } else {
            fallbackCopyToClipboard(text);
        }
    }

    // Fallback for older browsers
    function fallbackCopyToClipboard(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showCopyToast();
        } catch (err) {
            console.error('Fallback copy failed:', err);
        }
        
        document.body.removeChild(textArea);
    }

    // Show copy success toast
    function showCopyToast() {
        const toast = document.getElementById('copyToast');
        toast.classList.remove('translate-y-20', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
        
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
            toast.classList.remove('translate-y-0', 'opacity-100');
        }, 3000);
    }

    // Native share API (mobile)
    function shareNative(title, url) {
        if (navigator.share) {
            navigator.share({
                title: title,
                url: url
            }).then(() => {
                console.log('Shared successfully');
            }).catch(err => {
                console.log('Error sharing:', err);
            });
        }
    }

    // Hide native share button on desktop
    document.addEventListener('DOMContentLoaded', function() {
        if (!navigator.share) {
            const mobileOnlyElements = document.querySelectorAll('.mobile-only');
            mobileOnlyElements.forEach(el => el.style.display = 'none');
        }
    });
</script>

<style>
    /* Ensure mobile-only is visible only when share API is available */
    .mobile-only {
        display: flex;
    }
</style>
