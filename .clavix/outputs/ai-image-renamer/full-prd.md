# Product Requirements Document: AI Image Renamer

## Problem & Goal

WordPress users often upload images with generic filenames (e.g., `IMG_1234.jpg`), which is poor for SEO and accessibility. The goal is to build a minimalistic WordPress plugin that automatically renames image files during upload by generating descriptive, SEO-friendly names using Groq.com's Vision API (Llama Scout model).

## Requirements

### Must-Have Features

1.  **Automated Renaming on Upload**:
    - Hook into the WordPress media upload process.
    - Send the image to Groq's API with a specific prompt: "view this image and describe it in no more than 5 keywords. only return the output."
    - Rename the file using the API output.
2.  **Strict Filename Sanitization**:
    - Convert to lowercase.
    - Keep only alphanumeric characters and dashes.
    - Replace spaces and underscores with dashes.
    - Strip all other characters.
3.  **Secure Settings Page**:
    - Dedicated subpage under "Settings".
    - **Encrypted API Key Storage**: The Groq API key must be encrypted before being stored in the database. When viewed on the settings page, the encrypted version is shown (or the field is masked).
    - **Test Connection Button**: A button to verify the API key and connectivity to Groq.com.
4.  **Templating Architecture**:
    - Use the **Twig template engine** for all settings page views.
    - Organized folder structure (e.g., `views/` for Twig files).
5.  **Fallback Logic**:
    - If the API key is invalid or the service is down during upload, fallback to the original filename to prevent upload failure.

### Technical Requirements

- **Platform**: WordPress 6.x, PHP > 8.2.
- **API Communication**: Server-side PHP using WordPress standard methods (`wp_remote_post` or similar over HTTPS). No heavy external HTTP libraries.
- **Security**: Implement encryption for the API key. Use Composer to pull in a robust library (e.g., `defuse/php-encryption`) if native methods are insufficient.
- **Design**: Minimalistic UI that blends perfectly with the native WordPress dashboard experience.

### Suggested Future Integration Fields (Settings)

- **Post-processing Toggle**: Option to enable/disable auto-renaming.
- **File Type Filters**: Checkboxes to select which mime-types to process (JPEG, PNG, WebP, etc.).
- **Prompt Customization**: Text area to override the default Groq prompt.
- **Max Keywords**: Select field to limit the number of keywords generated.

## Out of Scope

- Bulk processing of existing media library images (v1 only handles new uploads).
- Frontend image optimization or compression.
- Integration with other AI providers (Groq only for now).

## Additional Context

- The plugin should stay lightweight and focus on the "upload and forget" experience once configured.
- Error messages on the settings page should be clear and helpful (e.g., "Invalid API Key").

---

_Generated with Clavix Planning Mode_
_Generated: 2026-01-10T00:05:00Z_
