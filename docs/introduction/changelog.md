# Changelog

All notable changes to AI Image Renamer are documented here. The format
follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and the project adheres
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-03-25

Initial public release.

### Added

- AI-powered image renaming on upload using Groq's Vision API
- Support for **JPEG**, **PNG**, **WebP**, and **GIF** image formats
- Optional attachment `alt` text population from the AI-generated description
- Groq API connection test button in the settings UI
- Two AI model options: **Llama 4 Maverick** (detailed) and **Llama 4 Scout** (fast)
- Configurable maximum keyword count (1–10, default 5)
- Encrypted API key storage using `defuse/php-encryption`
- Support for defining the API key via `AIR_API_KEY` constant in `wp-config.php`
- Support for defining the encryption key via `AIR_ENCRYPTION_KEY` constant in `wp-config.php`
- Graceful fallback: original filename is preserved if the API request fails
- 17 filter hooks and 5 action hooks for developer extensibility
- Diagnostics panel and model rate-limit overview in the admin UI
- Transient-based rate limiting for AJAX endpoints
- Full i18n support (`.pot` file included)

### Security

- API keys are encrypted at rest before being saved to the database
- All AJAX requests are protected by capability checks and nonce verification
- Input sanitization and output escaping throughout the admin interface

