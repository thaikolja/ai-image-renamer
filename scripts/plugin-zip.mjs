#!/usr/bin/env node

import {execSync}      from 'child_process';
import AdmZip          from 'adm-zip';
import fs              from 'fs';
import path            from 'path';
import {fileURLToPath} from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const pluginDir = path.join(__dirname, '..');
const packageName = 'ai-image-renamer';
const zipFileName = `${packageName}.zip`;
const zipPath = path.join(pluginDir, zipFileName);

try {
  // Run wp-scripts plugin-zip first
  console.log('Running wp-scripts plugin-zip...');
  execSync('npx wp-scripts plugin-zip', {cwd: pluginDir, stdio: 'inherit'});

  // Add assets/, vendor/, and views/ to the zip
  const zip = new AdmZip(zipPath);

  // Add assets directory (only built files and icons, not source files)
  if (fs.existsSync(path.join(pluginDir, 'assets'))) {
    console.log('Adding assets/ directory...');
    const assetsPath = path.join(pluginDir, 'assets');
    const addAssetsFolder = (dirPath, zipPathPrefix) => {
      const files = fs.readdirSync(dirPath);
      for (const file of files) {
        const filePath = path.join(dirPath, file);
        const stat = fs.statSync(filePath);
        if (stat.isDirectory()) {
          addAssetsFolder(filePath, zipPathPrefix + file + '/');
        } else {
          // Only include built files, icons, not source files
          const isBuiltFile =
                    file === 'index.js' ||
                    file === 'index.css' ||
                    file === 'index.asset.php' ||
                    dirPath.includes('icons');
          if (isBuiltFile) {
            zip.addLocalFile(filePath, zipPathPrefix);
          }
        }
      }
    };
    addAssetsFolder(assetsPath, `${packageName}/assets/`);
  }

  // Add vendor directory
  if (fs.existsSync(path.join(pluginDir, 'vendor'))) {
    console.log('Adding vendor/ directory...');
    zip.addLocalFolder(
        path.join(pluginDir, 'vendor'),
        `${packageName}/vendor`,
    );
  }

  // Add views directory
  if (fs.existsSync(path.join(pluginDir, 'views'))) {
    console.log('Adding views/ directory...');
    zip.addLocalFolder(
        path.join(pluginDir, 'views'),
        `${packageName}/views`,
    );
  }

  // Remove .md files and .DS_Store files from the zip
  console.log('Removing .md and .DS_Store files...');
  const entries = zip.getEntries();
  const entriesToDelete = [];
  for (const entry of entries) {
    if (entry.entryName.match(/\.md$/i) || entry.entryName.match(/\.DS_Store$/i)) {
      entriesToDelete.push(entry);
    }
  }
  for (const entry of entriesToDelete) {
    zip.deleteFile(entry);
  }

  // Save the modified zip
  zip.writeZip(zipPath);

  console.log(`✓ ${zipFileName} created with built assets/, vendor/, and views/ included`);
} catch (error) {
  console.error('Failed to create zip file:', error);
  process.exit(1);
}
