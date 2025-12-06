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
                'primary-dark': '#1F1D20',
                'primary-light': '#fdfbf8',
                'primary-gray': '#f2f2f2',
                white: '#fdfbf8',
                gray: {
                    50: '#fdfbf8',
                    100: '#fdfbf8',
                    200: '#f2f2f2',
                    300: '#f2f2f2',
                    400: '#f2f2f2',
                    500: '#f2f2f2',
                    600: '#f2f2f2',
                    700: '#1F1D20',
                    800: '#1F1D20',
                    900: '#1F1D20',
                },
                indigo: {
                    400: '#1F1D20',
                    500: '#1F1D20',
                    600: '#1F1D20',
                    700: '#1F1D20',
                },
                red: {
                    600: '#1F1D20',
                    700: '#1F1D20',
                },
            },
        },
    },

    plugins: [forms],
};
