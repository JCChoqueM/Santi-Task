import { defineConfig } from 'vite';
import FullReload from 'vite-plugin-full-reload';
import scssAutoIndex from './vite-plugins/scss-auto-index.js';
import fg from 'fast-glob';

// Busca cualquier .js en src/js/ y sus subcarpetas,
// EXCEPTO los que estén dentro de "modules/" (esos son solo para import)
const jsEntries = fg.sync([
    'src/js/**/*.js',
    '!src/js/modules/**'
]).reduce((entries, file) => {
    const nombre = file.replace('src/js/', '').replace('.js', '');
    entries[nombre] = file;
    return entries;
}, {});

export default defineConfig({
    publicDir: false,
    plugins: [
        scssAutoIndex('src/scss'),
        FullReload(['**/*.php'], {
            delay: 1000
        })
    ],
    css: {
        devSourcemap: true,
        preprocessorOptions: {
            scss: {
                includePaths: ['src/scss']
            }
        }
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        watch: {
            usePolling: true,
            interval: 1500,
            ignored: ['**/vendor/**',
                '**/node_modules/**',
                '**/public/build/**']
        }
    },
    build: {
        outDir: 'public/build',
        manifest: true,
        sourcemap: true,
        rollupOptions: {
            input: {
                ...jsEntries,
                styles: 'src/scss/app.scss'
            }
        }
    }
});