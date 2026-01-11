# Changelog

All notable changes to this project will be documented in this file.

## v1

### v1.0.0 - 2026-01-10

#### Added

- Initial release of AI Image Renamer.
- Integration with Groq's Vision API for image analysis.
- Support for multiple AI models: Llama Maverick and Scout.
- Advanced settings for custom prompts and keyword limits.
- Secure API key storage using AES-256-CTR encryption via `defuse/php-encryption`.
- Automatic population of image alt text based on AI analysis.
- Customizable supported file types (JPEG, PNG, WebP, GIF).
- Connection testing functionality in the admin settings.
- Multilingual-ready architecture with Twig templates.
- Modern admin UI with accessibility (WCAG 2.1) focus.
- PSR-4 autoloading and strict typing throughout the codebase.
- Graceful error handling and fallback to original filenames on API failure.

