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
                display: ['"Playfair Display"', 'Georgia', 'serif'],
                luxury: ['"Playfair Display"', 'Georgia', 'serif'],
                playfair: ['"Playfair Display"', 'Georgia', 'serif'],
            },
            colors: {
                gold: {
                    DEFAULT: '#D4AF37',
                    50: '#fbf8eb',
                    100: '#f5ecce',
                    200: '#ebd79b',
                    300: '#e5c76b',
                    400: '#d4af37',
                    500: '#be952c',
                    600: '#a07524',
                    700: '#7f5820',
                    800: '#684820',
                    900: '#583d1f',
                    950: '#33200f',
                },
                ink: {
                    DEFAULT: '#0b0b0c',
                    900: '#0b0b0c',
                    800: '#121214',
                    700: '#1a1a1d',
                    600: '#232327',
                },
                'deep-black': '#0a0a0a',
            },
        },
    },

    plugins: [forms],
};
