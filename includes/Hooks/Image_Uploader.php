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
        // Check if the service is enabled.
        if (! $this->groq_service->is_enabled()) {
            return $file;
        }

        // Check if there's an error with the upload.
        if (! empty($file['error'])) {
            return $file;
        }

        // Get the mime type.
        $mime_type = $file['type'] ?? '';

        // Check if this file type should be processed.
        if (! $this->groq_service->is_allowed_type($mime_type)) {
            return $file;
        }

        // Get the temporary file path.
        $tmp_path = $file['tmp_name'] ?? '';

        if (empty($tmp_path) || ! file_exists($tmp_path)) {
            return $file;
        }

        // Generate a description using the Groq API.
        $description = $this->groq_service->generate_description($tmp_path);

        // If generation failed, fall back to the original filename.
        if (false === $description || empty($description)) {
            return $file;
        }

        // Sanitize the generated description.
        $sanitized_name = File_Sanitizer::sanitize($description);

        // Get the original extension.
        $original_name = $file['name'] ?? '';
        $extension     = File_Sanitizer::get_extension($original_name);

        if (empty($extension)) {
            // Try to get extension from mime type.
            $extension = $this->get_extension_from_mime($mime_type);
        }

        // Build the new filename.
        $new_filename = File_Sanitizer::build_filename($sanitized_name, $extension);

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
