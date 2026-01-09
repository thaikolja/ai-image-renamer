# AI Image Renamer - Quick PRD

The "AI Image Renamer" is a minimalistic WordPress plugin designed to improve SEO by automatically renaming newly uploaded images using Groq.com's Llama Scout Vision API. The plugin hooks into the media upload process, generates 5 keywords describing the image, and sanitizes the output into a lowercase, alphanumeric-only filename where spaces and underscores are replaced by dashes. A key focus is the secure management of the Groq API key, which must be encrypted in the database and handled via a dedicated settings subpage.

Technically, the plugin requires WordPress 6.x and PHP > 8.2. It utilizes the Twig template engine for its settings UI and relies on WordPress's native HTTP methods for API requests. Security is paramount, necessitating the use of encryption (potentially via a Composer library like `defuse/php-encryption`) to protect the user's API key. The UI must blend seamlessly with the native WordPress dashboard aesthetic.

Out of scope for this version is the bulk renaming of existing media or processing of non-image files. Future-proofing includes settings for custom prompts, file type filtering, and toggling the auto-rename functionality. If the API service is unavailable, the plugin will gracefully fallback to the original filename.

---

*Generated with Clavix Planning Mode*
*Generated: 2026-01-10T00:05:00Z*
