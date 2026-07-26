import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'


export default defineConfig({
    plugins: [
        vue(),
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,


        }),
    ],

    // 修复你 ws 连接报错（Inertia 必备）
    server: {
        hmr: {
            host: 'localhost'
        }
    }
});
