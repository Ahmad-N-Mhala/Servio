import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // Enable dark mode with class strategy
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#FF6B35',
                    hover: '#E85A2D',
                    50: '#FFF0EB',
                    100: '#FFE1D6',
                    200: '#FFC3AD',
                    300: '#FFA585',
                    400: '#FF875C',
                    500: '#FF6B35',
                    600: '#E85A2D',
                    700: '#CC451F',
                    800: '#B03515',
                    900: '#94280E',
                },
                secondary: {
                    DEFAULT: '#2D3748',
                    50: '#F7FAFC',
                    100: '#EDF2F7',
                    200: '#E2E8F0',
                    300: '#CBD5E0',
                    400: '#A0AEC0',
                    500: '#718096',
                    600: '#4A5568',
                    700: '#2D3748',
                    800: '#1A202C',
                    900: '#171923',
                },
            },
        },
    },
    plugins: [],
};

