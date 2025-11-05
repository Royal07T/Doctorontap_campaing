import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            buildDirectory: 'build',
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
    },
    build: {
        outDir: 'public/build',
        manifest: 'manifest.json',
        // Optimize chunk size
        chunkSizeWarningLimit: 600,
        // Enable minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.logs in production
                drop_debugger: true,
            },
        },
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                css: 'resources/css/app.css',
            },
            output: {
                // Optimize chunk splitting
                manualChunks: {
                    'alpine': ['alpinejs', '@alpinejs/collapse'],
                },
            },
        },
        // Enable source maps for debugging (disable in production if not needed)
        sourcemap: false,
        // Enable CSS code splitting
        cssCodeSplit: true,
        // Enable asset inlining for small files
        assetsInlineLimit: 4096,
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['alpinejs', '@alpinejs/collapse'],
    },
    ssr: {
        noExternal: ['laravel-vite-plugin']
    },
});
