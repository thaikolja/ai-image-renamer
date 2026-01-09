# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-10

### Added
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

