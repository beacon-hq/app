import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import run from 'vite-plugin-run';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.tsx',
            refresh: false,
            detectTls: true,
        }),
        react(),
        run([
            {
                name: 'typescript-transformer',
                run: ['php', 'artisan', 'typescript:transform'],
                pattern: ['app/Values/**/*.php'],
            },
        ]),
    ],
});
