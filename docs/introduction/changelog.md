# Changelog

All notable changes to AI Image Renamer are documented on this page.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) and adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.0 — 2026-01-10

Initial release.

### Added

- AI-powered image renaming with Groq Vision API
- Support for **Llama 4 Maverick** and **Llama 4 Scout** models
- Encrypted API key storage using `defuse/php-encryption`
- Configurable file types: JPEG, PNG, WebP, AVIF, GIF
- Keyword limit configuration (1–10 keywords)
- Optional alt text auto-population
- Modern, accessible admin interface (WCAG 2.1 compliant)
- PHP 8.2+ and WordPress 6.0+ support
- Graceful API failure fallback (original filename preserved)
- Test connection functionality
- Twig-based template system for admin views
- Comprehensive error handling and debug logging
- Rate limiting for AJAX endpoints
- SVG sanitization for admin icons
- PSR-4 autoloading with service-based architecture
- Full WordPress Coding Standards (WPCS) compliance
- Extensibility hooks for Pro add-on development
