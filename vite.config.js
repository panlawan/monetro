import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [ 
                'resources/sass/app.scss',  // ใช้ SCSS
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    
    // Server configuration สำหรับ Docker (เก็บเดิม)
    server: {
        host: '0.0.0.0',
        port: 5173,
        watch: {
            usePolling: true,
        },
    },
    
    // Resolve aliases
    resolve: {
        alias: {
            '~bootstrap': 'node_modules/bootstrap',
            '~@fortawesome': 'node_modules/@fortawesome'
        }
    },
    
    // Optimize dependencies
    optimizeDeps: {
        include: [
            'bootstrap',
            '@popperjs/core',
            '@fortawesome/fontawesome-free',
            'alpinejs'
        ]
    },
    
    // CSS preprocessing options - ลด deprecation warnings
    css: {
        preprocessorOptions: {
            scss: {
                // ปิด deprecation warnings จาก Bootstrap
                quietDeps: true,
                silenceDeprecations: [
                    'import', 
                    'global-builtin', 
                    'color-functions', 
                    'mixed-decls',
                    'abs-percent'
                ],
                
                // เพิ่ม Bootstrap variables และ functions (เก็บไว้เดิม)
                additionalData: `
                    @import "bootstrap/scss/functions";
                    @import "bootstrap/scss/variables";
                    @import "bootstrap/scss/mixins";
                `
            }
        }
    },
    
    // Build configuration สำหรับ Vite 6.x
    build: {
        // Rollup options
        rollupOptions: {
            output: {
                assetFileNames: (assetInfo) => {
                    // Handle Font Awesome fonts
                    if (assetInfo.name && assetInfo.name.match(/\.(woff2?|eot|ttf|otf)$/)) {
                        return 'fonts/[name].[hash][extname]';
                    }
                    // Handle images
                    if (assetInfo.name && assetInfo.name.match(/\.(png|jpe?g|gif|svg|webp|avif)$/)) {
                        return 'images/[name].[hash][extname]';
                    }
                    // Handle other assets
                    return 'assets/[name].[hash][extname]';
                }
            }
        },
        
        // Chunk size warning limit
        chunkSizeWarningLimit: 1000,
        
        // CSS code splitting
        cssCodeSplit: true,
        
        // Minification options (ใช้ terser ที่เพิ่งติดตั้ง)
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: false, // เก็บ console.log สำหรับ debug
                drop_debugger: true,
                pure_funcs: ['console.debug', 'console.info']
            },
            mangle: {
                safari10: true
            }
        },
        
        // Target modern browsers
        target: 'es2015'
    }
});