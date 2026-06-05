import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    safelist: [
        'bg-rose-100',
        'text-rose-800',
        'text-rose-700',
        'ring-rose-200',
        'bg-orange-100',
        'text-orange-800',
        'text-orange-700',
        'ring-orange-200',
        'bg-amber-100',
        'text-amber-800',
        'text-amber-700',
        'ring-amber-200',
        'bg-emerald-100',
        'text-emerald-800',
        'text-emerald-700',
        'ring-emerald-200',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
