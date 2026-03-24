# AI Image Renamer

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/ai-image-renamer?style=flat-square&logo=wordpress&label=Version)](https://wordpress.org/plugins/ai-image-renamer/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/ai-image-renamer?style=flat-square&logo=wordpress&label=Downloads)](https://wordpress.org/plugins/ai-image-renamer/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/stars/ai-image-renamer?style=flat-square&logo=wordpress&label=Rating)](https://wordpress.org/support/plugin/ai-image-renamer/reviews/)
[![WordPress Plugin Required PHP](https://img.shields.io/wordpress/plugin/required-php/ai-image-renamer?style=flat-square&logo=php&label=PHP)](https://wordpress.org/plugins/ai-image-renamer/)
[![WordPress Plugin Tested WP Version](https://img.shields.io/wordpress/plugin/tested/ai-image-renamer?style=flat-square&logo=wordpress&label=Tested%20up%20to)](https://wordpress.org/plugins/ai-image-renamer/)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-blue?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)

**AI Image Renamer** transforms your WordPress media management by automatically generating SEO-friendly, descriptive filenames for uploaded images using Groq's powerful Vision API. Say goodbye to `IMG_1234.jpg` and hello to `golden-retriever-playing-park.jpg` — all without any manual effort.

The plugin intercepts every image upload via the WordPress Media Library, sends it to Groq's Vision API for analysis, and renames the file with descriptive, keyword-rich slugs before it is saved. Optionally, it also populates the image's `alt` attribute with the AI-generated description — improving both SEO and accessibility in a single step.

## Features

AI Image Renamer is built around a few core principles: automation, security, and extensibility.

- 🤖 **AI-Powered Naming** — Uses Llama 4 vision models (Maverick for detail, Scout for speed) to understand image content and generate meaningful filenames.
- 🔍 **SEO-Optimized** — Produces keyword-rich, lowercase, hyphenated slugs that search engines love.
- 🔒 **Secure by Default** — API keys are encrypted at rest using `defuse/php-encryption`. For maximum security, the encryption key can be defined in `wp-config.php`.
- 🎯 **Smart Alt Text** — When enabled, the AI description is also saved as the image's `alt` attribute, helping with accessibility compliance.
- ⚙️ **Fully Configurable** — Choose which image types to process, how many keywords to generate, and which AI model to use — all from a modern admin interface.
- 🛡️ **Graceful Fallback** — If the API is unreachable or returns an error, the original filename is preserved. Uploads never fail because of the plugin.
- 🚀 **Free to Use** — Powered by Groq's generous free-tier API. No credit card required.

## How It Works

When you upload an image, the plugin hooks into WordPress's `wp_handle_upload_prefilter` filter. The image is base64-encoded and sent to Groq's Vision API along with a configurable prompt. The API returns descriptive keywords, which are sanitized into an SEO-friendly slug and applied as the new filename. If alt text generation is enabled, the description is stored via a WordPress transient and applied once the attachment is created.

## Example

| Before | After |
| ------------------ | ------------------------------------------------- |
| `IMG_1234.jpg`     | `golden-retriever-playing-fetch-sunny-park.jpg`   |
| `photo_2026.png`   | `modern-office-desk-laptop-coffee.png`            |
| `screenshot.webp`  | `wordpress-dashboard-settings-page.webp`          |

## Supported Formats

The plugin processes **JPEG**, **PNG**, **WebP**, and **GIF** images. You can enable or disable individual formats in the [settings](/usage/settings).

## Requirements

You need **WordPress 6.0+**, **PHP 8.2+**, and a free Groq API key from [console.groq.com](https://console.groq.com/keys). Registration takes less than a minute and does not require a credit card.

## References

- [GitHub Repository](https://github.com/thaikolja/wp-ai-image-renamer)
- [GitLab Repository](https://gitlab.com/thaikolja/wp-ai-image-renamer)
- [WordPress.org Plugin Page](https://wordpress.org/plugins/ai-image-renamer/)
- [Groq API Console](https://console.groq.com/keys)

## License

AI Image Renamer is released under the [GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html) license.
