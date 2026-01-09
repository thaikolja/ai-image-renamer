# AI Image Renamer (WordPress Plugin)

Automatically rename uploaded images with AI for SEO-friendly, descriptive filenames. Powered by Groq’s Vision API.

[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-0073aa)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4)](https://www.php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-3da639)](https://www.gnu.org/licenses/gpl-2.0.html)

> Note: This repository contains a WordPress plugin. It runs inside WordPress, not as a standalone PHP app.

## What it does

When you upload an image to the WordPress Media Library, the plugin:

1. Sends the image to Groq’s Vision API.
2. Gets back a short set of descriptive keywords.
3. Builds a clean, SEO-friendly filename.
4. Renames the uploaded file.
5. Optionally populates the image **alt text**.

If the API call fails, the plugin falls back to the original filename, so uploads won’t break.

## Features

- AI-powered file naming (Groq Vision)
- SEO-friendly, human-readable filenames
- Optional auto alt-text
- Encrypted API key storage (`defuse/php-encryption`)
- Configurable models, prompt, keyword limit, supported file types

## Requirements

- WordPress **6.0+**
- PHP **8.2+**
- A Groq API key: https://console.groq.com/keys

## Installation

### Option A: Install as a normal WordPress plugin

1. Place this folder at: `wp-content/plugins/ai-image-renamer/`
2. Ensure Composer dependencies are installed (see below).
3. Activate the plugin in **WP Admin → Plugins**.

### Option B: Install from Git (recommended for development)

```bash
cd /path/to/your/wordpress/wp-content/plugins

git clone https://github.com/thaikolja/ai-image-renamer.git
cd ai-image-renamer

composer install --no-dev --optimize-autoloader
```

> Without `vendor/` the plugin shows an admin notice (“Composer dependencies not installed…”). That’s expected.

## Configuration (WP Admin)

1. Go to **Settings → AI Image Renamer**.
2. Paste your Groq API key.
3. Click **Test Connection**.
4. Choose model, keyword limit, file types, and (optional) alt-text behavior.
5. Save.

### Optional: define a dedicated encryption key

For maximum control, define an encryption key in `wp-config.php` (the plugin also works without this, but explicitly
setting a key makes environments more predictable):

```php
define('AIR_ENCRYPTION_KEY', 'put-a-long-random-secret-here');
```

You can generate a strong random value locally:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

## Example

- Before: `IMG_1234.jpg`
- After: `golden-retriever-playing-fetch-sunny-park.jpg`

## Security & Privacy

- API keys are encrypted before being stored.
- Images are only transmitted to Groq during upload processing.
- No image data is stored on external servers by this plugin.

## Troubleshooting

### “Composer dependencies not installed”

Run:

```bash
composer install --no-dev --optimize-autoloader
```

inside the plugin directory.

### API test fails

- Double-check the API key at https://console.groq.com/keys
- Ensure your server can make outbound HTTPS requests.
- Check WordPress logs (e.g. `wp-content/debug.log`) if enabled.

## Links

- Plugin documentation: https://docs.kolja-nolte.com/ai-image-renamer
- Plugin page (WordPress.org): https://wordpress.org/plugins/ (add the final slug once published)
- Issues: https://github.com/thaikolja/ai-image-renamer/issues

## License

GPL-2.0-or-later. See [`LICENSE`](LICENSE).

## Changelog

See [`CHANGELOG.md`](CHANGELOG.md).
