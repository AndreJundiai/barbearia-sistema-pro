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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                playfair: ['Playfair Display', 'serif'],
            },
            colors: {
                'gold': {
                    '50': '#fbf8eb',
                    '100': '#f5ecce',
                    '200': '#ebd79b',
                    '300': '#dfbb5f',
                    '400': '#d4af37',
                    '500': '#be952c',
                    '600': '#a07524',
                    '700': '#7f5820',
                    '800': '#684820',
                    '900': '#583d1f',
                    '950': '#33200f',
                },
                'deep-black': '#0a0a0a',
            },
        },
    },

    plugins: [forms],
};
