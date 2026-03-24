# Settings

All settings are found under **Media → AI Image Renamer** in your WordPress admin area.

## API Configuration

### Enable Auto-Rename

This is the main on/off switch for the plugin. When turned off, images are uploaded with their original filenames — the plugin won't do anything.

- **Default:** Off

### Groq API Key

Your API key connects the plugin to the [Groq](https://groq.com/) AI service. Without it, the plugin can't analyze images. Keys start with `gsk_` and are encrypted before being stored in your site's database, so they're never saved as plain text.

You can get a free API key from [console.groq.com](https://console.groq.com/keys).

::: tip Skip Database Storage Entirely
For maximum security, you can skip storing your API key in the database by defining the `AIR_API_KEY` constant directly in your `wp-config.php` file:

```php
define('AIR_API_KEY', 'gsk_your_api_key_here');
```

When this constant is set, the plugin securely uses it and ignores any key saved in the database.
:::

::: warning Keep your encryption key safe
By default, the encryption key is stored in your database. For better security, you can add it to your site's [`wp-config.php`](https://developer.wordpress.org/advanced-administration/wordpress/wp-config/) file:

```php
define('AIR_ENCRYPTION_KEY', 'your-long-random-key-here');
```

This way, even if someone gains access to your database, they won't be able to read your API key. See the [Encryption Service documentation](/api/functions#encryption-service) for details.
:::

### AI Model

The plugin offers two AI models to choose from. Both analyze your images and generate descriptions, but they have different strengths:

| Model | Best For | Speed |
| ------------ | ------------------------------------------ | ------------- |
| **Maverick** | Detailed, creative image analysis          | ~600 tokens/s |
| **Scout**    | Faster processing with good accuracy       | ~750 tokens/s |

**Maverick** is selected by default and is recommended for most users. Choose **Scout** if speed is more important to you than extra detail.

### Use as Alt Text

When this is turned on, the AI-generated description is also saved as the image's [alt text](https://www.w3.org/WAI/tutorials/images/). Alt text is what screen readers use to describe images to visually impaired users, and search engines also use it to understand your images.

Enabling this option automatically sets the keyword limit to **10** to generate richer descriptions.

- **Default:** Off

## File Types

Choose which image formats the plugin should process. Uncheck any formats you'd like to skip.

| Format | Enabled by Default |
| ------ | ------------------ |
| JPEG   | ✅ Yes             |
| PNG    | ✅ Yes             |
| WebP   | ✅ Yes             |
| AVIF   | ✅ Yes             |
| GIF    | ❌ No              |

## Advanced Settings

### Max Keywords

Controls how many keywords the AI generates for the filename. More keywords mean longer, more descriptive filenames.

- **Range:** 1–10
- **Default:** 5

::: tip
A value of **3–5** works well for most sites. Use higher values if you want very detailed filenames.
:::

## Reading Settings with Code

If you're a developer and want to access these settings in your own code, they're stored as a single [option](https://developer.wordpress.org/plugins/settings/options-api/) called `air_options`:

```php
$options = get_option( 'air_options', [] );

// Check if auto-rename is enabled
$enabled = $options['enabled'] ?? false;

// Get the current model
$model = $options['model'] ?? 'meta-llama/llama-4-maverick-17b-128e-instruct';
```
