import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    // App is exclusively dark-themed: make the dark variant deterministic
    // (via the `dark` class on <html>) instead of OS preference, so paired
    // `dark:` utilities — e.g. on the shared input components — always apply.
    darkMode: 'class',

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
            // Shades intermediárias usadas na UI que não existem na paleta
            // padrão do Tailwind (600↔700 e 800↔900). Sem estas definições as
            // classes `*-650` / `zinc-850` não geram estilo algum.
            colors: {
                indigo: { 650: '#493fd8' },
                violet: { 650: '#7531e3' },
                zinc: { 850: '#1f1f23' },
            },
            // Chevrons de navegação que oscilam suavemente para frente e para
            // trás, indicando a direção da ação de forma contínua e leve.
            keyframes: {
                'chevron-nudge-right': {
                    '0%, 100%': { transform: 'translateX(0)' },
                    '50%': { transform: 'translateX(3px)' },
                },
                'chevron-nudge-left': {
                    '0%, 100%': { transform: 'translateX(0)' },
                    '50%': { transform: 'translateX(-3px)' },
                },
            },
            animation: {
                'chevron-right': 'chevron-nudge-right 1.3s ease-in-out infinite',
                'chevron-left': 'chevron-nudge-left 1.3s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
