=== AI Image Renamer ===
Contributors: thaikolja
Tags: images, media, seo, accessibility, ai
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.com/paypalme/thaikolja/10/

Automatically rename newly uploaded images with AI-generated, descriptive filenames and optional alt text.

== Description ==

AI Image Renamer helps you replace filenames like `IMG_1234.jpg` with cleaner, more descriptive names based on the actual image content.

The plugin integrates with Groq's Vision API during upload and can:

* generate descriptive filenames for new image uploads,
* optionally save cleaned alt text for the uploaded attachment,
* fall back to the original filename if the AI request fails,
* let you choose supported file types and the Groq vision model,
* keep API key handling user-friendly while still supporting a stronger `wp-config.php` setup.

The settings screen is available under **Media → AI Image Renamer**.

= Example =

* Original filename: `IMG_1234.jpg`
* Possible generated filename: `golden-retriever-playing-fetch-park.jpg`

= Supported image types =

* JPEG / JPG
* PNG
* WebP
* AVIF
* GIF

= Security notes =

* The plugin supports storing the Groq API key directly in `wp-config.php` via `AIR_API_KEY`.
* Saved API keys are encrypted at rest.
* The plugin warns administrators when the encryption key is still stored in the database instead of `wp-config.php`.
* All AJAX requests are protected by capability checks and nonces.

= Developer hooks =

The plugin includes actions and filters for advanced integrations, including upload processing, prompt customization, model lists, file types, and generated filenames.

== Installation ==

= From WordPress.org =

1. Install and activate **AI Image Renamer**.
2. Go to **Media → AI Image Renamer**.
3. Enter your Groq API key.
4. Click **Test Connection**.
5. Save your settings.

= Manual installation =

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin in WordPress.
3. Open **Media → AI Image Renamer**.
4. Add your Groq API key and save the settings.

== Frequently Asked Questions ==

= Does this rename existing images? =

No. Version 1.0.0 only processes new uploads.

= What happens if Groq is unavailable? =

The upload continues with the original filename.

= Can I store the API key outside the database? =

Yes. Define `AIR_API_KEY` in `wp-config.php` and the plugin will use that value instead of a saved key.

= Can I also keep the encryption key out of the database? =

Yes. Define `AIR_ENCRYPTION_KEY` in `wp-config.php`.

= Which file types can be processed? =

JPEG, PNG, WebP, AVIF, and GIF are supported.

= Does the plugin create alt text? =

It can save a cleaned version of the generated description as the attachment alt text when that option is enabled.

== Screenshots ==

1. General settings and quick start information
2. API key configuration and connection test
3. Groq model selection
4. File type selection
5. Advanced settings and diagnostics

== Changelog ==

= 1.0.0 =
* Initial public release
* AI-based image renaming for new uploads
* Optional attachment alt text support
* Groq API connection test in the admin UI
* Support for JPEG, PNG, WebP, AVIF, and GIF
* Encrypted API key storage with `wp-config.php` support
* Diagnostics and model limit information in the settings UI

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Third-Party Services ==

This plugin connects to a third-party service.

= Groq =

AI Image Renamer uses the Groq API to analyze uploaded images and generate descriptive filenames.

**What is sent:**

* the uploaded image,
* a short text instruction,
* the selected model identifier.

**When it is sent:**

* only when the plugin is enabled,
* only during supported image uploads,
* only when a valid Groq API key is configured.

**Service links:**

* Groq: https://groq.com/
* Groq Console: https://console.groq.com/keys
* Terms of Use: https://groq.com/terms-of-use/
* Privacy Policy: https://groq.com/privacy-policy/

== Support ==

* Documentation: https://docs.kolja-nolte.com/ai-image-renamer
* Support forum: https://wordpress.org/support/plugin/ai-image-renamer/
* Source repository: https://gitlab.com/thaikolja/wp-ai-image-renamer
