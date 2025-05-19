import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/chart-sidebar.js',
                'resources/js/fix-placeholders.js',
                'resources/js/msgmassacontatos.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
