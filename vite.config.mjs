/*
 * @name:           AI Image Renamer
 * @wordpress       Uses AI to rename images during upload for SEO-friendly filenames.
 * @author          Kolja Nolte <kolja.nolte@gmail.com>
 * @copyright       2025-2026 (C) Kolja Nolte
 * @see             https://docs.kolja-nolte.com/ai-image-renamer
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Released under the GNU General Public License v2 or later.
 * See: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package AIR
 * @license GPL-2.0-or-later
 */

import {defineConfig}  from 'vite';
import path            from 'path';
import {fileURLToPath} from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const isProduction = process.env.NODE_ENV === 'production';

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
        assetFileNames: (assetInfo) => { // BACKUP: assetFileNames
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
    sourcemap:     true,
    minify:        isProduction,
    cssMinify:     isProduction,
    jsSourceMap:   true,
    cssCodeSplit:  true,
  },
  server:  {
    port:  3000,
    host:  true,
    watch: {
      ignored: ['**/vendor/**', '**/node_modules/**'],
    },
  },
});
