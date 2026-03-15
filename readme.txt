=== AI Image Renamer ===
Contributors:       thaikolja
Tags:               rename files, ai, automation, images, seo
Requires at least:  6.0
Tested up to:       6.9
Requires PHP:       8.2
Stable tag:         1.0.0
License:            GPLv2 or later
License URI:        https://www.gnu.org/licenses/gpl-2.0.html
Donate:             https://www.paypal.com/paypalme/thaikolja/10/

Automatically rename uploaded images using AI for SEO-friendly, descriptive filenames. Powered by Groq's free Vision API.

== Description ==

**AI Image Renamer** transforms your WordPress media management by automatically generating SEO-friendly, descriptive filenames for uploaded images using Groq's powerful Vision API. Say goodbye to `IMG_1234.jpg` and hello to `golden-retriever-playing-park.jpg` – all without any manual work!

= Key Features =

* 🤖 **AI-Powered Naming** - Uses advanced Llama vision models (Maverick & Scout)
* 🔍 **SEO-Optimized** - Generates keyword-rich, descriptive filenames automatically
* 🔒 **Secure Storage** - API keys encrypted with industry-standard encryption
* 🎯 **Smart Alt Text** - Optionally auto-populate image alt attributes for accessibility
* ⚙️ **Fully Customizable** - Configure file types, prompts, keyword limits, and AI models
* 🎨 **Modern Interface** - Clean, accessible admin UI with WCAG 2.1 compliance
* 🚀 **Free to Use** - Leverages Groq's generous free tier API
* 🛡️ **Graceful Fallback** - Keeps original filename if API fails

= How It Works =

1. You upload an image to WordPress Media Library
2. Plugin sends image to Groq's Vision API
3. AI analyzes the image and returns descriptive keywords
4. Keywords are sanitized into SEO-friendly format
5. Image is saved with the new descriptive filename
6. (Optional) Alt text is automatically populated

= Example Transformation =

* **Original**: `IMG_1234.jpg`
* **AI Analysis**: "Golden Retriever, Playing Fetch, Sunny Park, Happy Dog"
* **New Filename**: `golden-retriever-playing-fetch-sunny-park.jpg`

= Supported Image Formats =

* JPEG / JPG
* PNG
* WebP
* GIF

= Requirements =

* WordPress 6.0 or higher
* PHP 8.2 or higher
* Free Groq API key from [console.groq.com](https://console.groq.com/keys)

= Privacy & Security =

* API keys are encrypted before storage using `defuse/php-encryption`
* Images are sent to Groq's API only during upload processing
* No data is stored on external servers by this plugin
* Fully GDPR compliant

**Data sent to Groq API:** When you upload an image, the plugin sends the image data (base64 encoded) to Groq's servers for AI analysis. The response containing descriptive keywords is used to rename the file. See the Third-Party Services section below for more details.

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to **Plugins → Add New**
3. Search for "AI Image Renamer"
4. Click "Install Now" and then "Activate"
5. Go to **Settings → AI Image Renamer**
6. Enter your free Groq API key from [console.groq.com](https://console.groq.com/keys)
7. Click "Test Connection" to verify
8. Configure your preferences and save

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins → Add New → Upload Plugin**
4. Choose the ZIP file and click "Install Now"
5. Click "Activate Plugin"
6. Follow steps 5-8 from Automatic Installation above

= Composer Installation (for developers) =

1. Navigate to your WordPress plugins directory:
   `cd wp-content/plugins/`
2. Clone or download the plugin
3. Install dependencies:
   `composer install --no-dev`
4. Activate via WordPress admin

== Frequently Asked Questions ==

= Is this plugin free? =

Yes! The plugin itself is 100% free and open source (GPL-2.0). It uses Groq's API which offers a generous free tier for personal and commercial use.

= Do I need a Groq API key? =

Yes, you need a free API key from [console.groq.com](https://console.groq.com/keys). Registration is free and takes less than a minute.

= What happens if the API fails? =

The plugin gracefully falls back to the original filename. Your upload will never fail due to API issues.

= Can I customize the AI prompt? =

Yes! Go to **Settings → AI Image Renamer → Advanced Settings** and enter your custom prompt. This allows you to control how the AI describes your images.

= Does this work with existing images? =

Currently, the plugin only processes new uploads. Bulk renaming of existing images is planned for a future Pro version.

= Which AI models are available? =

You can choose between two models:
* **Maverick** - High-performance model for detailed, creative analysis
* **Scout** - Lightweight model optimized for speed and efficiency

= Can I disable renaming for specific uploads? =

Yes, simply toggle "Enable Auto-Rename" off in the settings before uploading, then turn it back on after.

= Is my API key secure? =

Absolutely! API keys are encrypted using `defuse/php-encryption` before being stored in your database. For maximum security, you can define the encryption key in `wp-config.php`.

= Does this affect site performance? =

The API call happens during upload, so there's a slight delay (usually 1-3 seconds) while the image is being analyzed. Regular site visitors won't notice any performance impact.

= What file types are supported? =

JPEG, PNG, WebP, and GIF. You can enable/disable specific types in the settings.

= Can I use this on multiple sites? =

Yes! Each site needs its own API key configuration, but you can use the same Groq API key across multiple WordPress installations.

== Screenshots ==

1. Settings page with API key configuration
2. Model selection (Maverick vs Scout)
3. File type selection and advanced options
4. Test connection feature
5. Example of renamed image in Media Library
6. Alt text automatically populated

== Changelog ==

= 1.0.0 - 2026-01-10 =
* Initial release
* AI-powered image renaming with Groq Vision API
* Support for Llama Maverick and Scout models
* Encrypted API key storage
* Configurable file types (JPEG, PNG, WebP, GIF)
* Custom prompt support
* Keyword limit configuration (1-10)
* Optional alt text auto-population
* Modern, accessible admin interface
* WCAG 2.1 compliant
* PHP 8.2+ and WordPress 6.0+ support
* Graceful API failure fallback
* Test connection functionality
* Twig-based template system
* Comprehensive error handling

== Upgrade Notice ==

= 1.0.0 =
Initial release. Automatic image renaming with AI-powered descriptions.

== Technical Details ==

= Architecture =

* **PSR-4** autoloading with Composer
* **Strict types** enabled throughout
* **Type hints** on all methods
* **Service-based architecture** for clean separation of concerns
* **Twig templating** for maintainable admin views
* **WordPress Coding Standards** compliant

= Security Features =

* API keys encrypted with `defuse/php-encryption`
* Nonce verification on all AJAX requests
* Capability checks (`manage_options`)
* Input sanitization and output escaping
* No direct file access allowed
* Secure key storage with wp-config.php support

= Performance =

* Twig template caching enabled (disabled in WP_DEBUG mode)
* Optimized autoloader
* Minimal database queries
* Asynchronous API calls
* No frontend impact

= Hooks & Filters =

**Actions:**
* `wp_handle_upload_prefilter` - Main upload interception
* `add_attachment` - Alt text population

**Filters:**
* `air_mime_to_ext` - Modify MIME type to extension mapping
* Custom filters available in future versions

= Developer Resources =

* GitHub Repository: [https://github.com/thaikolja/wp-ai-image-renamer](https://github.com/thaikolja/wp-ai-image-renamer)
* GitLab Repository: [https://gitlab.com/thaikolja/wp-ai-image-renamer](https://gitlab.com/thaikolja/wp-ai-image-renamer)
* Documentation: [https://docs.kolja-nolte.com/ai-image-renamer](https://docs.kolja-nolte.com/ai-image-renamer)
* Report Issues: [https://github.com/thaikolja/wp-ai-image-renamer/issues](https://github.com/thaikolja/wp-ai-image-renamer/issues)
* Support Forum: [https://wordpress.org/support/plugin/ai-image-renamer/](https://wordpress.org/support/plugin/ai-image-renamer/)

== Third-Party Services ==

This plugin connects to external third-party services to provide its functionality. By using this plugin, you agree to the terms of service and privacy policies of these providers.

= Groq API =

This plugin uses the [Groq API](https://groq.com/) to analyze uploaded images and generate descriptive keywords for SEO-friendly filenames.

**What data is sent:**

* Image data (base64 encoded) is sent to Groq's servers when you upload an image to WordPress
* A text prompt asking the AI to describe the image

**When data is sent:**

* Only when "Enable Auto-Rename" is turned on in the plugin settings
* Only during the image upload process
* Only for image types you have enabled in the settings (JPEG, PNG, WebP, GIF)

**Service links:**

* Groq Website: [https://groq.com/](https://groq.com/)
* Terms of Service: [https://groq.com/terms-of-use/](https://groq.com/terms-of-use/)
* Privacy Policy: [https://groq.com/privacy-policy/](https://groq.com/privacy-policy/)
* API Documentation: [https://console.groq.com/docs](https://console.groq.com/docs)

**Note:** You must obtain your own API key from [console.groq.com](https://console.groq.com/keys) to use this plugin. Groq offers a generous free tier.

== Credits ==

* **Groq** - For providing the powerful Vision API
* **Defuse** - For the PHP encryption library (`defuse/php-encryption`)
* **Twig** - For the template engine (`twig/twig`)
* **WordPress Community** - For ongoing support and inspiration

== Support ==

For support, please use the [WordPress.org support forums](https://wordpress.org/support/plugin/ai-image-renamer/) or visit the [official documentation](https://docs.kolja-nolte.com/ai-image-renamer).

== Contribute ==

This plugin is open source! Contributions are welcome on GitLab or GitHub.
