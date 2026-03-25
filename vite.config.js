import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '')
    const devHost = env.VITE_DEV_SERVER_HOST || 'localhost'
    const devPort = Number(env.VITE_DEV_SERVER_PORT || 5173)

    return {
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        css: {
            preprocessorOptions: {
                scss: {
                    api: 'modern-compiler',
                },
            },
        },
        server: {
            host: '0.0.0.0',
            port: devPort,
            strictPort: true,
            hmr: {
                host: devHost,
                port: devPort,
                protocol: env.VITE_DEV_SERVER_PROTOCOL || 'ws',
            },
            watch: {
                usePolling: env.VITE_USE_POLLING === 'true',
            },
        },
    }
})
