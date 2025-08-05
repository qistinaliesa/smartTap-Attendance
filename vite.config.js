import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
          input: ['resources/sass/app.scss', 'resources/js/app.js']
, // 👈 updated this line
            refresh: true,
        }),
        tailwindcss(),
    ],
});

