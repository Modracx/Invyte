/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
    plugins: [vue()],
    root: 'resources/js',
    build: {
        outDir: resolve(__dirname, 'public/assets'),
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: resolve(__dirname, 'resources/js/main.js'),
        }
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js')
        }
    },
    server: {
        port: 5173,
        proxy: {
            '/api': {
                target: 'http://example.demo/ims',
                changeOrigin: true
            }
        }
    }
})
