<?php

/**
 * Image Uploader Hook.
 *
 * @package AIR\Hooks
 */

declare(strict_types=1);

namespace AIR\Hooks;

use AIR\Services\Groq_Service;
use AIR\Utils\File_Sanitizer;

/**
 * Class Image_Uploader
 *
 * Hooks into WordPress upload process to rename images.
 */
class Image_Uploader
{

    /**
     * Groq service instance.
     *
     * @var Groq_Service
     */
    private Groq_Service $groq_service;

    /**
     * Constructor.
     *
     * @param Groq_Service $groq_service Groq service instance.
     */
    public function __construct(Groq_Service $groq_service)
    {
        $this->groq_service = $groq_service;
    }

    /**
     * Initialize the upload hooks.
     *
     * @return void
     */
    public function init(): void
    {
        error_log('AI Image Renamer: Registering wp_handle_upload_prefilter hook');
        add_filter('wp_handle_upload_prefilter', array($this, 'process_upload'));
    }

    /**
     * Process an uploaded file and rename it using AI.
     *
     * @param array $file The uploaded file data from $_FILES.
     *
     * @return array The modified file data.
     */
    public function process_upload(array $file): array
    {
        // ALWAYS log for debugging.
        error_log('AI Image Renamer: ========== UPLOAD START ==========');
        error_log('AI Image Renamer: process_upload called for file: ' . ($file['name'] ?? 'unknown'));
        error_log('AI Image Renamer: File array: ' . print_r($file, true));

        // Check if the service is enabled.
        if (! $this->groq_service->is_enabled()) {
            error_log('AI Image Renamer: Service is NOT enabled, skipping.');
            return $file;
        }

        error_log('AI Image Renamer: Service IS enabled, proceeding...');

        // Check if there's an error with the upload.
        if (! empty($file['error'])) {
            error_log('AI Image Renamer: Upload has error, skipping. Error code: ' . $file['error']);
            return $file;
        }

        // Get the mime type.
        $mime_type = $file['type'] ?? '';
        error_log('AI Image Renamer: File mime type: ' . $mime_type);

        // Check if this file type should be processed.
        if (! $this->groq_service->is_allowed_type($mime_type)) {
            error_log('AI Image Renamer: Mime type "' . $mime_type . '" not allowed, skipping.');
            return $file;
        }

        error_log('AI Image Renamer: Mime type IS allowed.');

        // Get the temporary file path.
        $tmp_path = $file['tmp_name'] ?? '';
        error_log('AI Image Renamer: Temp file path: ' . $tmp_path);

        if (empty($tmp_path)) {
            error_log('AI Image Renamer: Temp path is EMPTY, skipping.');
            return $file;
        }

        if (! file_exists($tmp_path)) {
            error_log('AI Image Renamer: Temp file does NOT exist at: ' . $tmp_path);
            return $file;
        }

        error_log('AI Image Renamer: Temp file EXISTS, calling Groq API...');

        // Generate a description using the Groq API.
        $description = $this->groq_service->generate_description($tmp_path);

        error_log('AI Image Renamer: Groq API returned: ' . ($description === false ? 'FALSE' : '"' . $description . '"'));

        // If generation failed, fall back to the original filename.
        if (false === $description || empty($description)) {
            error_log('AI Image Renamer: generate_description returned empty/false, keeping original name.');
            return $file;
        }

        error_log('AI Image Renamer: Generated description: ' . $description);

        // Sanitize the generated description.
        $sanitized_name = File_Sanitizer::sanitize($description);
        error_log('AI Image Renamer: Sanitized name: ' . $sanitized_name);

        // Get the original extension.
        $original_name = $file['name'] ?? '';
        $extension     = File_Sanitizer::get_extension($original_name);

        if (empty($extension)) {
            // Try to get extension from mime type.
            $extension = $this->get_extension_from_mime($mime_type);
        }

        // Build the new filename.
        $new_filename = File_Sanitizer::build_filename($sanitized_name, $extension);

        error_log('AI Image Renamer: RENAMING "' . $original_name . '" TO "' . $new_filename . '"');
        error_log('AI Image Renamer: ========== UPLOAD END ==========');

        // Update the file array with the new name.
        $file['name'] = $new_filename;

        return $file;
    }

    /**
     * Get file extension from mime type.
     *
     * @param string $mime_type The mime type.
     *
     * @return string The file extension.
     */
    private function get_extension_from_mime(string $mime_type): string
    {
        $mime_to_ext = array(
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        );

        return $mime_to_ext[$mime_type] ?? 'jpg';
    }
}
