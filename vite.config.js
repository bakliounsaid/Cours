import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        hmr: { host: 'localhost' },
        watch: {
            // Docker sous Windows : les événements de fichiers ne traversent pas le montage
            usePolling: process.env.VITE_POLLING === 'true',
            ignored: ['**/storage/framework/views/**', '**/vendor/**'],
        },
    },
});
