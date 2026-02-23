# FAQ

Answers to the most common questions about AI Image Renamer.

## Is this plugin free?

Yes — completely. The plugin is open source and released under the [GPL-2.0](https://www.gnu.org/licenses/gpl-2.0.html) license. It connects to [Groq's](https://groq.com/) AI service, which also offers a generous free plan.

## Do I need a Groq API key?

Yes. The API key is what lets the plugin communicate with the AI service that analyzes your images. You can get one for free at [console.groq.com](https://console.groq.com/keys) — registration takes less than a minute and no credit card is required.

## What happens if the AI service is down?

Your image upload still works — it just keeps its original filename. The plugin is designed to never interfere with your uploads, even if the AI service is temporarily unavailable.

## Which AI models are available?

Two models are available, both from [Meta's Llama 4](https://ai.meta.com/) family:

- **Maverick** — More detailed image analysis. Recommended for most users.
- **Scout** — Faster processing, still accurate. Good if speed matters more.

Both models are free to use through Groq's API.

## Does this work with images already in my Media Library?

Not yet — the plugin only processes images at the time of upload. Bulk renaming of existing images is planned for a future version.

## Can I customize how images are named?

Yes! If you're comfortable with a bit of PHP, you can use the [`air_prompt`](/api/filter-hooks#air-prompt) [filter](https://developer.wordpress.org/plugins/hooks/filters/) to change the instructions given to the AI. See the [examples page](/api/examples) for ready-to-use code snippets.

For non-developers, the [settings page](/usage/settings) lets you control how many keywords are used and which AI model to choose.

## Can I prevent specific uploads from being renamed?

You can turn the **"Enable Auto-Rename"** toggle off before uploading, then turn it back on afterward.

Developers can also use the [`air_should_process_upload`](/api/filter-hooks#air-should-process-upload) filter to skip certain uploads automatically based on custom conditions.

## Is my API key stored securely?

Yes. Your API key is [encrypted](https://en.wikipedia.org/wiki/Encryption) before being saved in the database using a well-known security library. For even better protection, you can store the encryption key in your site's [`wp-config.php`](https://developer.wordpress.org/advanced-administration/wordpress/wp-config/) file — see [Settings → API Key](/usage/settings#groq-api-key) for instructions.

## Does this slow down my website?

No. The AI analysis happens only during the upload process and typically takes 1–3 seconds. Your website visitors won't notice any difference, because the plugin has no effect on the pages they see.

## What image formats are supported?

JPEG, PNG, WebP, AVIF, and GIF. You can enable or disable specific formats in the [settings](/usage/settings#file-types).

## Can I use this plugin on multiple websites?

Absolutely. Each site needs its own settings configured, but you can reuse the same Groq API key across as many WordPress installations as you like.

## What information is sent to Groq?

When you upload an image, the plugin sends two things to Groq's servers:

1. The image data (so the AI can analyze it)
2. A text prompt (instructions for describing the image)

This only happens during the upload — never when someone visits your site. No personal or visitor data is collected. See the [Groq Privacy Policy](https://groq.com/privacy-policy/) for details on how Groq handles data.

## How do I report a bug?

You can reach out through either of these channels:

- [WordPress.org Support Forum](https://wordpress.org/support/plugin/ai-image-renamer/)
- [GitHub Issues](https://github.com/thaikolja/wp-ai-image-renamer/issues)
