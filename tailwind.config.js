import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Identidad North College: azul marino + rojo de acento.
                navy: {
                    50: '#eef3fa',
                    100: '#d8e2f2',
                    200: '#b3c5e4',
                    300: '#86a1d0',
                    400: '#5677b5',
                    500: '#36579a',
                    600: '#26437d',
                    700: '#1c3464',
                    800: '#15294f',
                    900: '#0f2c59',
                    950: '#0a1c3a',
                },
                accent: {
                    50: '#fdedef',
                    100: '#fbd6da',
                    200: '#f6acb4',
                    500: '#d72638',
                    600: '#b81d2e',
                    700: '#961726',
                },
            },
        },
    },

    plugins: [forms],
};
