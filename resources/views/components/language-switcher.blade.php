@props(['currentLocale' => app()->getLocale()])

<div class="relative" x-data="{ open: false }">
    <button 
        @click="open = !open" 
        @keydown.escape.window="open = false" 
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-primary-dark hover:bg-primary-dark hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-dark/20 transition-all duration-300"
        title="{{ __('shop.language') }}"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm font-medium hidden sm:inline">
            @if($currentLocale === 'ar')
                العربية
            @elseif($currentLocale === 'nl')
                Nederlands
            @else
                English
            @endif
        </span>
        <svg class="h-4 w-4 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div 
        x-show="open" 
        @click.outside="open = false" 
        x-transition
        class="absolute {{ app()->getLocale() === 'ar' ? 'left-0 rtl:text-right' : 'right-0' }} mt-2 w-48 bg-white rounded-xl shadow-lg border border-primary-gray py-2 z-50"
        style="display: none;"
    >
        <a 
            href="javascript:void(0)" 
            onclick="switchLanguage('en')"
            class="flex items-center gap-2 px-4 py-2 text-sm {{ $currentLocale === 'en' ? 'bg-primary-dark text-white font-semibold' : 'text-primary-dark hover:bg-primary-dark hover:text-white' }} transition-all duration-200"
        >
            <span class="text-lg">🇬🇧</span>
            <span>English</span>
            @if($currentLocale === 'en')
                <svg class="h-4 w-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            @endif
        </a>
        <a 
            href="javascript:void(0)" 
            onclick="switchLanguage('nl')"
            class="flex items-center gap-2 px-4 py-2 text-sm {{ $currentLocale === 'nl' ? 'bg-primary-dark text-white font-semibold' : 'text-primary-dark hover:bg-primary-dark hover:text-white' }} transition-all duration-200"
        >
            <span class="text-lg">🇳🇱</span>
            <span>Nederlands</span>
            @if($currentLocale === 'nl')
                <svg class="h-4 w-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            @endif
        </a>
        <a 
            href="javascript:void(0)" 
            onclick="switchLanguage('ar')"
            class="flex items-center gap-2 px-4 py-2 text-sm {{ $currentLocale === 'ar' ? 'bg-primary-dark text-white font-semibold' : 'text-primary-dark hover:bg-primary-dark hover:text-white' }} transition-all duration-200"
        >
            <span class="text-lg">🇸🇦</span>
            <span>العربية</span>
            @if($currentLocale === 'ar')
                <svg class="h-4 w-4 ml-auto" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            @endif
        </a>
    </div>
</div>

<script>
function switchLanguage(locale) {
    // Store preference in localStorage
    localStorage.setItem('preferred_locale', locale);
    
    // Get current URL and preserve all query parameters
    const url = new URL(window.location.href);
    url.searchParams.set('locale', locale);
    
    // Reload page with new locale
    window.location.href = url.toString();
}

// Set locale from localStorage on page load if no locale is in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (!urlParams.has('locale')) {
        const savedLocale = localStorage.getItem('preferred_locale');
        if (savedLocale && ['en', 'nl', 'ar'].includes(savedLocale)) {
            const url = new URL(window.location.href);
            url.searchParams.set('locale', savedLocale);
            window.location.href = url.toString();
        }
    }
});
</script>
