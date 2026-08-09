import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#F472B6',
                    50: '#FFF7FB',
                    100: '#FCE7F3',
                    200: '#FBCFE8',
                    300: '#F9A8D4',
                    400: '#F472B6',
                    500: '#EC4899',
                    600: '#DB2777',
                    700: '#BE185D',
                },
                accent: '#EC4899',
                soft: '#FBCFE8',
                ink: '#1F2937',
                fertile: '#3B82F6',
                success: '#22C55E',
                warning: '#F59E0B',
                danger: '#EF4444',
            },

            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },

            boxShadow: {
                glass: '0 2px 8px -2px rgba(16, 24, 40, 0.08)',
                soft: '0 1px 2px rgba(16, 24, 40, 0.05), 0 6px 20px -8px rgba(16, 24, 40, 0.08)',
                glow: '0 4px 14px -2px rgba(219, 39, 119, 0.22)',
                card: '0 1px 2px rgba(16, 24, 40, 0.04), 0 4px 14px -6px rgba(16, 24, 40, 0.07)',
            },

            backgroundImage: {
                'bloom-gradient':
                    'linear-gradient(135deg, #FDF2F8 0%, #FCE7F3 100%)',
                'bloom-soft':
                    'linear-gradient(180deg, #FFFFFF 0%, #FAFAF9 100%)',
                'glass-shine':
                    'linear-gradient(120deg, rgba(255,255,255,0.6) 0%, rgba(255,255,255,0.15) 100%)',
            },

            keyframes: {
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(14px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'fade-in': {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-400px 0' },
                    '100%': { backgroundPosition: '400px 0' },
                },
            },

            animation: {
                'fade-in-up': 'fade-in-up 0.45s ease-out both',
                'fade-in': 'fade-in 0.3s ease-out both',
                shimmer: 'shimmer 1.4s linear infinite',
            },
        },
    },

    plugins: [forms],
};
