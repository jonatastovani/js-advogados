// import commonjs from '@rollup/plugin-commonjs';
import laravel from 'laravel-vite-plugin';
import path from 'path'; // <--- precisa disso
import { defineConfig } from 'vite';

import { globSync } from 'glob'; // Use globSync com import na versão ESM

// import { nodeResolve } from '@rollup/plugin-node-resolve';

export default defineConfig({
    // base: '/build/',
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    plugins: [
        // nodeResolve(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/sass/app.scss',
                'resources/js/app.js',
                ...globSync('resources/js/views/**/*.js'), // Sincroniza todos os arquivos js da pasta resources/js/views
                ...globSync('resources/js/commons/**/*.js'), // Sincroniza todos os arquivos js da pasta resources/js/views
            ],
            refresh: true,
        }),
        // commonjs() // Adiciona suporte a módulos CommonJS
    ],
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true
            }
        }
    },
    // permite acessar por vários domínios no dev
    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: {
            origin: '*'
        },
        strictPort: true,
        hmr: {
            host: 'jsadvogados.byteforge.com.br'
        }
    },
    // build: {
    //     rollupOptions: {
    //         output: {
    //             exports: 'auto', // Isso vai tratar arquivos que não exportam nada explicitamente
    //         },
    //     },
    // },
});
