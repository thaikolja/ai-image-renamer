<?php

/**
 * Groq API Service.
 *
 * @package AIR\Services
 */

declare(strict_types=1);

namespace AIR\Services;

/**
 * Class Groq_Service
 *
 * Handles communication with the Groq Vision API.
 */
class Groq_Service
{

    /**
     * Groq API endpoint.
     *
     * @var string
     */
    private const API_ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

    /**
     * Default vision model.
     *
     * @var string
     */
    private const DEFAULT_MODEL = 'llama-3.2-90b-vision-preview';

    /**
     * Default prompt for image description.
     *
     * @var string
     */
    private const DEFAULT_PROMPT = 'View this image and describe it in no more than 5 keywords. Only return the output.';

    /**
     * Encryption service instance.
     *
     * @var Encryption_Service
     */
    private Encryption_Service $encryption_service;

    /**
     * Constructor.
     *
     * @param Encryption_Service $encryption_service Encryption service instance.
     */
    public function __construct(Encryption_Service $encryption_service)
    {
        $this->encryption_service = $encryption_service;
    }

    /**
     * Get the decrypted API key.
     *
     * @return string|false The API key or false if not available.
     */
    private function get_api_key(): string|false
    {
        $options = get_option('air_options', array());

        if (empty($options['api_key'])) {
            return false;
        }

        return $this->encryption_service->decrypt($options['api_key']);
    }

    /**
     * Get the prompt to use for image description.
     *
     * @return string The prompt text.
     */
    private function get_prompt(): string
    {
        $options = get_option('air_options', array());

        if (! empty($options['custom_prompt'])) {
            return $options['custom_prompt'];
        }

        $max_keywords = $options['max_keywords'] ?? 5;

        return sprintf(
            'View this image and describe it in no more than %d keywords. Only return the output.',
            $max_keywords
        );
    }

    /**
     * Check if the Groq service is enabled.
     *
     * @return bool True if enabled.
     */
    public function is_enabled(): bool
    {
        $options = get_option('air_options', array());
        return ! empty($options['enabled']) && ! empty($options['api_key']);
    }

    /**
     * Check if a mime type is allowed for processing.
     *
     * @param string $mime_type The mime type to check.
     *
     * @return bool True if allowed.
     */
    public function is_allowed_type(string $mime_type): bool
    {
        $options    = get_option('air_options', array());
        $file_types = $options['file_types'] ?? array('image/jpeg', 'image/png', 'image/webp', 'image/gif');

        return in_array($mime_type, $file_types, true);
    }

    /**
     * Test the API connection.
     *
     * @return true|string True on success, error message on failure.
     */
    public function test_connection(): true|string
    {
        $api_key = $this->get_api_key();

        if (false === $api_key) {
            return __('No API key configured.', 'ai-image-renamer');
        }

        // Make a simple models request to verify the key.
        $response = wp_remote_get(
            'https://api.groq.com/openai/v1/models',
            array(
                'timeout' => 15,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ),
            )
        );

        if (is_wp_error($response)) {
            return $response->get_error_message();
        }

        $code = wp_remote_retrieve_response_code($response);

        if (200 !== $code) {
            $body    = wp_remote_retrieve_body($response);
            $decoded = json_decode($body, true);

            if (isset($decoded['error']['message'])) {
                return $decoded['error']['message'];
            }

            return sprintf(
                /* translators: %d: HTTP status code */
                __('API returned HTTP %d', 'ai-image-renamer'),
                $code
            );
        }

        return true;
    }

    /**
     * Generate a description for an image.
     *
     * @param string $image_path Absolute path to the image file.
     *
     * @return string|false The generated keywords or false on failure.
     */
    public function generate_description(string $image_path): string|false
    {
        if (! $this->is_enabled()) {
            return false;
        }

        $api_key = $this->get_api_key();

        if (false === $api_key) {
            return false;
        }

        // Read and encode the image.
        if (! file_exists($image_path) || ! is_readable($image_path)) {
            error_log('AI Image Renamer: Image file not found or not readable: ' . $image_path);
            return false;
        }

        $image_data = file_get_contents($image_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        if (false === $image_data) {
            return false;
        }

        $mime_type    = mime_content_type($image_path);
        $base64_image = base64_encode($image_data); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
        $data_url     = sprintf('data:%s;base64,%s', $mime_type, $base64_image);

        // Build the request payload.
        $payload = array(
            'model'      => self::DEFAULT_MODEL,
            'messages'   => array(
                array(
                    'role'    => 'user',
                    'content' => array(
                        array(
                            'type' => 'text',
                            'text' => $this->get_prompt(),
                        ),
                        array(
                            'type'      => 'image_url',
                            'image_url' => array(
                                'url' => $data_url,
                            ),
                        ),
                    ),
                ),
            ),
            'max_tokens' => 100,
        );

        // Make the API request.
        $response = wp_remote_post(
            self::API_ENDPOINT,
            array(
                'timeout' => 30,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type'  => 'application/json',
                ),
                'body'    => wp_json_encode($payload),
            )
        );

        if (is_wp_error($response)) {
            error_log('AI Image Renamer: API request failed: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if (200 !== $code) {
            error_log('AI Image Renamer: API returned HTTP ' . $code . ': ' . $body);
            return false;
        }

        $decoded = json_decode($body, true);

        if (! isset($decoded['choices'][0]['message']['content'])) {
            error_log('AI Image Renamer: Unexpected API response format: ' . $body);
            return false;
        }

        return trim($decoded['choices'][0]['message']['content']);
    }
}
