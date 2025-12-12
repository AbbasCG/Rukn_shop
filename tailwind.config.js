import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Signika', ...defaultTheme.fontFamily.sans],
                heading: ['Tilt Warp', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Brand Primary Scale (Charcoal to Off-White)
                primary: {
                    50: '#FDFBF8',   // Warm off-white
                    100: '#F9F6F0',  // Lightest warm gray
                    200: '#EDE8DD',  // Light warm gray
                    300: '#D9D2C4',  // Soft taupe
                    400: '#B8AE9C',  // Medium taupe
                    500: '#8B8072',  // Warm mid gray
                    600: '#6B5F4E',  // Dark taupe
                    700: '#4A3F30',  // Deep brown-gray
                    800: '#1F1D20',  // Brand charcoal
                    900: '#151314',  // Deepest charcoal
                },
                // Secondary Accent (Terracotta Orange)
                accent: {
                    50: '#FAEAE5',   // Lightest terracotta
                    100: '#F5D5CC',  // Light terracotta
                    200: '#ECBAA8',  // Soft terracotta
                    300: '#E28B6D',  // Light terracotta
                    400: '#D86945',  // Primary terracotta (main CTA color)
                    500: '#C34F2E',  // Hover terracotta
                    600: '#9D3F24',  // Active terracotta
                    700: '#762F1B',  // Dark terracotta
                    800: '#4F1F12',  // Deeper terracotta
                    900: '#281009',  // Deepest terracotta
                },
                // Neutral System
                neutral: {
                    50: '#FAFAFA',
                    100: '#F5F5F5',
                    200: '#E8E8E8',
                    300: '#D4D4D4',
                    400: '#A3A3A3',
                    500: '#737373',
                    600: '#525252',
                    700: '#404040',
                    800: '#262626',
                    900: '#171717',
                },
                // Semantic Colors
                success: {
                    DEFAULT: '#22C55E',
                    light: '#86EFAC',
                    dark: '#15803D',
                },
                warning: {
                    DEFAULT: '#F59E0B',
                    light: '#FCD34D',
                    dark: '#B45309',
                },
                error: {
                    DEFAULT: '#EF4444',
                    light: '#FCA5A5',
                    dark: '#B91C1C',
                },
                info: {
                    DEFAULT: '#3B82F6',
                    light: '#93C5FD',
                    dark: '#1E40AF',
                },
                // Legacy aliases for backward compatibility
                'primary-dark': '#1F1D20',
                'primary-light': '#FDFBF8',
                'primary-gray': '#F2F2F2',
            },
        },
    },

    plugins: [forms],
};
