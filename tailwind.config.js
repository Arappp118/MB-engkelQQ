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
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                mc: {
                    bg:       '#0F1117',
                    sidebar:  '#1A1C23',
                    card:     '#1E2029',
                    border:   '#2A2D3A',
                    muted:    '#6B7280',
                    text:     '#E5E7EB',
                    orange:   '#FF6B00',
                    'orange-hover': '#E55F00',
                    'orange-light': 'rgba(255,107,0,0.12)',
                },
            },
            keyframes: {
                'fade-in': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'slide-in': {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(0)' },
                },
                'pulse-orange': {
                    '0%, 100%': { boxShadow: '0 0 0 0 rgba(255,107,0,0.4)' },
                    '50%': { boxShadow: '0 0 0 6px rgba(255,107,0,0)' },
                },
            },
            animation: {
                'fade-in':     'fade-in 0.3s ease-out both',
                'slide-in':    'slide-in 0.25s ease-out both',
                'pulse-orange':'pulse-orange 2s infinite',
            },
        },
    },

    plugins: [forms],
};
