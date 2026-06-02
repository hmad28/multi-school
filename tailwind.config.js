import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#f8fafc',
                    100: '#dbeafe',
                    600: '#3b82f6',
                    700: '#2563eb',
                    800: '#1d4ed8',
                    900: '#1e293b',
                    950: '#0f172a',
                },
                canvas: '#ffffff',
                surface: '#f1f5f9',
                ink: {
                    DEFAULT: '#0f172a',
                    muted: '#475569',
                    faint: '#94a3b8',
                },
                line: {
                    DEFAULT: '#e2e8f0',
                    strong: '#cbd5e1',
                },
            },
            borderRadius: {
                card: '1rem',
                btn: '0.625rem',
            },
            boxShadow: {
                card: '0 1px 2px rgb(15 23 42 / 0.04), 0 4px 16px rgb(15 23 42 / 0.04)',
                featured: '0 0 0 1px rgb(37 99 235 / 0.2), 0 8px 32px -4px rgb(37 99 235 / 0.18)',
                visual: '0 24px 48px -12px rgba(15,23,42,0.12)',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
