# AI Image Renamer

A WordPress plugin that automatically renames uploaded images using Groq's Vision API for SEO-friendly filenames.

## Features

- **Automatic Renaming**: Uploads trigger AI-powered image analysis via Groq's Llama Scout vision model
- **SEO-Friendly**: Generates descriptive, keyword-based filenames
- **Secure**: API keys are encrypted before storage using `defuse/php-encryption`
- **Customizable**: Configure file types, custom prompts, and keyword limits
- **Fallback**: Gracefully falls back to original filename if API fails

## Requirements

- WordPress 6.0+
- PHP 8.2+
- Composer
- Groq API Key (free at [console.groq.com](https://console.groq.com))

## Installation

1. Clone or download this plugin to `wp-content/plugins/ai-image-renamer/`
2. Install Composer dependencies:

   ```bash
   cd wp-content/plugins/ai-image-renamer
   composer install
   ```

3. Activate the plugin in WordPress admin
4. Go to **Settings → AI Image Renamer**
5. Enter your Groq API key and save

## Configuration

### Settings Page

Navigate to **Settings → AI Image Renamer** to configure:

| Setting | Description |
|---------|-------------|
| **Groq API Key** | Your API key (encrypted on save) |
| **Enable Auto-Rename** | Toggle automatic renaming on/off |
| **Allowed Types** | Select which image types to process (JPEG, PNG, WebP, GIF) |
| **Custom Prompt** | Override the default AI prompt |
| **Max Keywords** | Limit the number of keywords generated (1-10) |

### Optional: Manual Encryption Key

For maximum security, define the encryption key in `wp-config.php`:

```php
define( 'AIR_ENCRYPTION_KEY', 'your-generated-key-here' );
```

To generate a key, run this PHP command:

```php
<?php
require 'vendor/autoload.php';
$key = \Defuse\Crypto\Key::createNewRandomKey();
echo $key->saveToAsciiSafeString();
```

## How It Works

1. User uploads an image via WordPress Media Library
2. Plugin intercepts the upload via `wp_handle_upload_prefilter`
3. Image is sent to Groq's Vision API (Llama Scout model)
4. API returns descriptive keywords
5. Keywords are sanitized (lowercase, alphanumeric, dashes only)
6. File is renamed before being saved to the uploads directory

### Sanitization Rules

- Convert to lowercase
- Replace spaces and underscores with dashes
- Remove all non-alphanumeric characters (except dashes)
- Remove consecutive dashes
- Example: `"Golden Retriever, Playing, Park!"` → `golden-retriever-playing-park`

## File Structure

```
ai-image-renamer/
├── ai-image-renamer.php        # Plugin entry point
├── composer.json               # Composer dependencies
├── includes/
│   ├── Plugin.php              # Main bootstrap class
│   ├── Admin/
│   │   └── Settings_Page.php   # WP Settings API integration
│   ├── Hooks/
│   │   └── Image_Uploader.php  # Upload hook handler
│   ├── Services/
│   │   ├── Encryption_Service.php
│   │   ├── Groq_Service.php
│   │   └── Template_Engine.php
│   └── Utils/
│       └── File_Sanitizer.php
├── views/
│   └── admin/
│       └── settings.twig
└── assets/
    ├── css/
    │   └── admin.css
    └── js/
        └── admin.js
```

## License

GPL-2.0-or-later

## Author

Kolja
