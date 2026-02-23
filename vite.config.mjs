import {defineConfig}  from 'vite';
import path            from 'path';
import {fileURLToPath} from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default defineConfig({
  plugins: [],
  build:   {
    outDir:        'assets',
    emptyOutDir:   false,
    rollupOptions: {
      input:  {
        main: path.resolve(__dirname, 'assets/js/main.js'),
      },
      output: {
        entryFileNames: 'js/index.js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: (assetInfo) => {
          // Rename style.css to index.css to match entry point
          if (assetInfo.name === 'style.css') {
            return 'css/index.css';
          }
          if (assetInfo.name.endsWith('.css')) {
            return 'css/[name][extname]';
          }
          return '[name][extname]';
        },
      },
    },
    sourcemap:     false,
    minify:        'terser',
    cssCodeSplit:  false,
  },
  server:  {
    port:  3000,
    host:  true,
    watch: {
      ignored: ['**/vendor/**', '**/node_modules/**'],
    },
  },
});
