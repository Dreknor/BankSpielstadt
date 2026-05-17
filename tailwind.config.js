/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    safelist: [
        // Akzent-Farben für die wiederverwendbare Kind-Suche (boerse.partials.kind_suche)
        { pattern: /(border|bg|text|hover:bg|hover:text|focus:border)-(emerald|sky|violet|amber|rose)-(100|200|300|400|500|700|800|900)/ },
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', 'system-ui', 'sans-serif'],
            },
            colors: {
                brand: {
                    50:  '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                },
            },
            boxShadow: {
                kid: '0 6px 20px -6px rgba(37,99,235,.35)',
            },
        },
    },
    plugins: [],
};
