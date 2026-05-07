import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                // Adicionando sua paleta ao Tailwind
                primary: {
                    DEFAULT: '#5a7253',
                    50: '#f4f6f3',
                    100: '#e7ece5',
                    200: '#cfdacd',
                    300: '#abc0a7',
                    400: '#83a07e',
                    500: '#5a7253',
                    600: '#4a5e44',
                    700: '#3c4b38',
                    800: '#323e2f',
                    900: '#2a3428',
                    950: '#161c15'
                }
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};