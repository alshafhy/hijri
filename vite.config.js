import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/scss/core.scss',
                'resources/scss/overrides.scss',
                'resources/assets/scss/style.scss',
                'resources/assets/scss/style-rtl.scss',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '~': path.resolve(__dirname, 'node_modules'),
            '@': path.resolve(__dirname, 'resources'),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                loadPaths: [
                    path.resolve(__dirname, 'node_modules'),
                    path.resolve(__dirname, 'resources/assets'),
                    path.resolve(__dirname, 'resources'),
                ],
            },
        },
    },
    build: {
        manifest: true,
        sourcemap: false,
        cssCodeSplit: true,
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('bootstrap') || id.includes('@popperjs')) {
                            return 'vendor-bootstrap';
                        }
                        if (id.includes('jquery')) {
                            return 'vendor-jquery';
                        }
                        if (id.includes('axios')) {
                            return 'vendor-axios';
                        }

                        return 'vendor';
                    }
                },
            },
        },
    },
});
