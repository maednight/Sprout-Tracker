    import { defineConfig } from 'vite'
    import laravel from 'laravel-vite-plugin'
    import vue from '@vitejs/plugin-vue'

    export default defineConfig({
    plugins: [
        laravel({
        input: [
            'resources/css/app.css',
            'resources/css/transactions/transactions.css',
            'resources/js/app.js',
        ],
        refresh: true,
        }),
        vue(),
    ],
    })
