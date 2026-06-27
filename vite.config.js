import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/landing.css',
                'resources/css/map.css',
                'resources/css/refactor.css',
                'resources/css/admin/dashboard.css',

                'resources/js/app.js',
                'resources/js/landing.js',
                'resources/js/map.js',
                'resources/js/location-picker.js',
                'resources/js/refactor/umkm-submission-modal.js',
                'resources/js/refactor/admin-charts.js',
                'resources/js/refactor/admin-lokasi-map.js',
                'resources/js/refactor/admin-menu-list.js',
                'resources/js/refactor/map-modals.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
