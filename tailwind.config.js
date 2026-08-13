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
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
            },

            /* ------------------------------------------------------------------
             * Design tokens — colors are defined once as CSS variables in
             * resources/css/app.css and referenced here so Tailwind utilities
             * and any plain CSS can share the same source of truth.
             * ------------------------------------------------------------------ */
            colors: {
                primary: {
                    50: 'rgb(var(--color-primary-50) / <alpha-value>)',
                    100: 'rgb(var(--color-primary-100) / <alpha-value>)',
                    200: 'rgb(var(--color-primary-200) / <alpha-value>)',
                    300: 'rgb(var(--color-primary-300) / <alpha-value>)',
                    400: 'rgb(var(--color-primary-400) / <alpha-value>)',
                    500: 'rgb(var(--color-primary-500) / <alpha-value>)',
                    600: 'rgb(var(--color-primary-600) / <alpha-value>)',
                    700: 'rgb(var(--color-primary-700) / <alpha-value>)',
                    800: 'rgb(var(--color-primary-800) / <alpha-value>)',
                    900: 'rgb(var(--color-primary-900) / <alpha-value>)',
                    950: 'rgb(var(--color-primary-950) / <alpha-value>)',
                },
                secondary: {
                    50: 'rgb(var(--color-secondary-50) / <alpha-value>)',
                    100: 'rgb(var(--color-secondary-100) / <alpha-value>)',
                    200: 'rgb(var(--color-secondary-200) / <alpha-value>)',
                    300: 'rgb(var(--color-secondary-300) / <alpha-value>)',
                    400: 'rgb(var(--color-secondary-400) / <alpha-value>)',
                    500: 'rgb(var(--color-secondary-500) / <alpha-value>)',
                    600: 'rgb(var(--color-secondary-600) / <alpha-value>)',
                    700: 'rgb(var(--color-secondary-700) / <alpha-value>)',
                    800: 'rgb(var(--color-secondary-800) / <alpha-value>)',
                    900: 'rgb(var(--color-secondary-900) / <alpha-value>)',
                    950: 'rgb(var(--color-secondary-950) / <alpha-value>)',
                },
                neutral: {
                    50: 'rgb(var(--color-neutral-50) / <alpha-value>)',
                    100: 'rgb(var(--color-neutral-100) / <alpha-value>)',
                    200: 'rgb(var(--color-neutral-200) / <alpha-value>)',
                    300: 'rgb(var(--color-neutral-300) / <alpha-value>)',
                    400: 'rgb(var(--color-neutral-400) / <alpha-value>)',
                    500: 'rgb(var(--color-neutral-500) / <alpha-value>)',
                    600: 'rgb(var(--color-neutral-600) / <alpha-value>)',
                    700: 'rgb(var(--color-neutral-700) / <alpha-value>)',
                    800: 'rgb(var(--color-neutral-800) / <alpha-value>)',
                    900: 'rgb(var(--color-neutral-900) / <alpha-value>)',
                    950: 'rgb(var(--color-neutral-950) / <alpha-value>)',
                },
                success: {
                    50: 'rgb(var(--color-success-50) / <alpha-value>)',
                    100: 'rgb(var(--color-success-100) / <alpha-value>)',
                    200: 'rgb(var(--color-success-200) / <alpha-value>)',
                    300: 'rgb(var(--color-success-300) / <alpha-value>)',
                    400: 'rgb(var(--color-success-400) / <alpha-value>)',
                    500: 'rgb(var(--color-success-500) / <alpha-value>)',
                    600: 'rgb(var(--color-success-600) / <alpha-value>)',
                    700: 'rgb(var(--color-success-700) / <alpha-value>)',
                    800: 'rgb(var(--color-success-800) / <alpha-value>)',
                    900: 'rgb(var(--color-success-900) / <alpha-value>)',
                    950: 'rgb(var(--color-success-950) / <alpha-value>)',
                },
                warning: {
                    50: 'rgb(var(--color-warning-50) / <alpha-value>)',
                    100: 'rgb(var(--color-warning-100) / <alpha-value>)',
                    200: 'rgb(var(--color-warning-200) / <alpha-value>)',
                    300: 'rgb(var(--color-warning-300) / <alpha-value>)',
                    400: 'rgb(var(--color-warning-400) / <alpha-value>)',
                    500: 'rgb(var(--color-warning-500) / <alpha-value>)',
                    600: 'rgb(var(--color-warning-600) / <alpha-value>)',
                    700: 'rgb(var(--color-warning-700) / <alpha-value>)',
                    800: 'rgb(var(--color-warning-800) / <alpha-value>)',
                    900: 'rgb(var(--color-warning-900) / <alpha-value>)',
                    950: 'rgb(var(--color-warning-950) / <alpha-value>)',
                },
                danger: {
                    50: 'rgb(var(--color-danger-50) / <alpha-value>)',
                    100: 'rgb(var(--color-danger-100) / <alpha-value>)',
                    200: 'rgb(var(--color-danger-200) / <alpha-value>)',
                    300: 'rgb(var(--color-danger-300) / <alpha-value>)',
                    400: 'rgb(var(--color-danger-400) / <alpha-value>)',
                    500: 'rgb(var(--color-danger-500) / <alpha-value>)',
                    600: 'rgb(var(--color-danger-600) / <alpha-value>)',
                    700: 'rgb(var(--color-danger-700) / <alpha-value>)',
                    800: 'rgb(var(--color-danger-800) / <alpha-value>)',
                    900: 'rgb(var(--color-danger-900) / <alpha-value>)',
                    950: 'rgb(var(--color-danger-950) / <alpha-value>)',
                },
                info: {
                    50: 'rgb(var(--color-info-50) / <alpha-value>)',
                    100: 'rgb(var(--color-info-100) / <alpha-value>)',
                    200: 'rgb(var(--color-info-200) / <alpha-value>)',
                    300: 'rgb(var(--color-info-300) / <alpha-value>)',
                    400: 'rgb(var(--color-info-400) / <alpha-value>)',
                    500: 'rgb(var(--color-info-500) / <alpha-value>)',
                    600: 'rgb(var(--color-info-600) / <alpha-value>)',
                    700: 'rgb(var(--color-info-700) / <alpha-value>)',
                    800: 'rgb(var(--color-info-800) / <alpha-value>)',
                    900: 'rgb(var(--color-info-900) / <alpha-value>)',
                    950: 'rgb(var(--color-info-950) / <alpha-value>)',
                },
            },

            /* ------------------------------------------------------------------
             * Type scale
             * ------------------------------------------------------------------ */
            fontSize: {
                '2xs': ['0.6875rem', { lineHeight: '1rem' }],
                'display': ['2.5rem', { lineHeight: '1.15', letterSpacing: '-0.02em' }],
            },
            fontWeight: {
                heading: '600',
            },

            /* ------------------------------------------------------------------
             * Radius / shadow scales — consistent surfaces
             * ------------------------------------------------------------------ */
            borderRadius: {
                control: '0.625rem',
                card: '1rem',
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(15 23 42 / 0.05), 0 1px 3px 0 rgb(15 23 42 / 0.06)',
                soft: '0 10px 25px -5px rgb(15 23 42 / 0.08)',
                lift: '0 20px 35px -12px rgb(15 23 42 / 0.18)',
            },

            /* ------------------------------------------------------------------
             * Micro-interactions
             * ------------------------------------------------------------------ */
            keyframes: {
                'fade-in-up': {
                    '0%': { opacity: '0', transform: 'translateY(6px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-400px 0' },
                    '100%': { backgroundPosition: '400px 0' },
                },
            },
            animation: {
                'fade-in-up': 'fade-in-up 0.3s ease-out both',
                shimmer: 'shimmer 1.4s linear infinite',
            },
        },
    },

    plugins: [forms],
};
