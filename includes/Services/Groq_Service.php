<?php
/*
 * @name:           AI Image Renamer
 * @wordpress       Uses AI to rename images during upload for SEO-friendly filenames.
 * @author          Kolja Nolte <kolja.nolte@gmail.com>
 * @copyright       2025-2026 (C) Kolja Nolte
 * @see             https://docs.kolja-nolte.com/ai-image-renamer
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Released under the GNU General Public License v2 or later.
 * See: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package AIR
 * @license GPL-2.0-or-later
 */

/**
 * Groq API Service.
 *
 * @package AIR\Services
 */

declare( strict_types=1 );

namespace AIR\Services;

/**
 * Class Groq_Service
 *
 * Handles communication with the Groq Vision API.
 */
class Groq_Service {
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
	private const DEFAULT_MODEL = 'meta-llama/llama-4-scout-17b-16e-instruct';

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
	public function __construct( Encryption_Service $encryption_service ) {
		$this->encryption_service = $encryption_service;
	}

	/**
	 * Check if the API key is defined as a constant in wp-config.php.
	 *
	 * @return bool True if AIR_API_KEY constant is defined and non-empty.
	 */
	public static function has_api_key_constant(): bool {
		return \defined( 'AIR_API_KEY' ) && ! empty( AIR_API_KEY );
	}

	/**
	 * Get the decrypted API key.
	 *
	 * Prioritizes the AIR_API_KEY constant over the database-stored key.
	 *
	 * @return string|false The API key or false if not available.
	 */
	private function get_api_key(): string|false {
		// Check for constant-defined API key first (recommended method).
		if ( self::has_api_key_constant() ) {
			return AIR_API_KEY;
		}

		$options = \get_option( 'air_options', [] );

		if ( empty( $options['api_key'] ) ) {
			return false;
		}

		return $this->encryption_service->decrypt( $options['api_key'] );
	}

	/**
	 * Get the prompt to use for image description.
	 *
	 * @return string The prompt text.
	 */
	private function get_prompt(): string {
		$options = \get_option( 'air_options', [] );

		$set_alt = isset( $options['set_alt_text'] ) && '1' === (string) $options['set_alt_text'];

		// If alt text is enabled, force 10 keywords regardless of max_keywords setting.
		$max_keywords = $set_alt ? 10 : ( $options['max_keywords'] ?? 5 );

		$prompt = \sprintf( /* translators: %d: Maximum number of keywords */ \_n( 'View this image and describe it in no more than %d keyword into English. Only return the output.',
		                                                                           'View this image and describe it in no more than %d keywords in English. Only return the output.',
		                                                                           $max_keywords,
		                                                                           'ai-image-renamer' ),
		                                                                      $max_keywords );

		/**
		 * Filter the AI prompt used for image description.
		 * Pro can customize or completely replace the prompt.
		 *
		 * @param string $prompt       The prompt text.
		 * @param int    $max_keywords The maximum number of keywords.
		 * @param bool   $set_alt      Whether alt text feature is enabled.
		 *
		 * @since 1.0.0
		 */
		return \apply_filters( 'air_prompt', $prompt, $max_keywords, $set_alt );
	}

	/**
	 * Check if the Groq service is enabled.
	 *
	 * @return bool True if enabled.
	 */
	final public function is_enabled(): bool {
		$options = \get_option( 'air_options', [] );

		// Check enabled flag - handle both boolean and string "1" from database.
		$enabled = isset( $options['enabled'] ) && ( true === $options['enabled'] || '1' === $options['enabled'] || 1 === $options['enabled'] );

		// Check if API key exists (constant takes priority).
		$has_key = self::has_api_key_constant() || ! empty( $options['api_key'] );

		return $enabled && $has_key;
	}

	/**
	 * Check if a mime type is allowed for processing.
	 *
	 * @param string $mime_type The mime type to check.
	 *
	 * @return bool True if allowed.
	 */
	final public function is_allowed_type( string $mime_type ): bool {
		$options    = \get_option( 'air_options', [] );
		$file_types = $options['file_types'] ?? [ 'image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif' ];

		/**
		 * Filter the allowed file types.
		 * Pro can expand this list to include more formats.
		 *
		 * @param array  $file_types The allowed MIME types.
		 * @param string $mime_type  The MIME type being checked.
		 *
		 * @since 1.0.0
		 */
		$file_types = \apply_filters( 'air_allowed_file_types', $file_types, $mime_type );

		return in_array( $mime_type, $file_types, true );
	}

	/**
	 * Test the API connection.
	 *
	 * @param string|null $api_key Optional API key to test. If null, uses the saved key.
	 *
	 * @return true|string True on success, error message on failure.
	 */
	final public function test_connection( ?string $api_key = null ): true|string {
		if ( empty( $api_key ) ) {
			$api_key = $this->get_api_key();
		}

		if ( empty( $api_key ) ) {
			return \__( 'No API key configured.', 'ai-image-renamer' );
		}

		// Make a simple models request to verify the key.
		$response = \wp_safe_remote_get( 'https://api.groq.com/openai/v1/models',
		                                 [
			                                 'timeout' => 15,
			                                 'headers' => [
				                                 'Authorization' => 'Bearer ' . $api_key,
				                                 'Content-Type'  => 'application/json',
				                                 'Origin'        => \site_url(),
				                                 'Referer'       => \admin_url(),
			                                 ],
		                                 ] );

		if ( \is_wp_error( $response ) ) {
			// Sanitize the error message.
			return \esc_html( $response->get_error_message() );
		}

		$code = \wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {
			$body    = \wp_remote_retrieve_body( $response );
			$decoded = json_decode( $body, true );

			if ( isset( $decoded['error']['message'] ) ) {
				// Sanitize the API error message.
				return \esc_html( $decoded['error']['message'] );
			}

			return \sprintf( /* translators: %d: HTTP status code */ \__( 'API returned HTTP %d', 'ai-image-renamer' ),
			                                                         $code );
		}

		return true;
	}

	/**
	 * Get the selected model ID.
	 *
	 * @return string
	 */
	private function get_model(): string {
		$options = \get_option( 'air_options', [] );

		return $options['model'] ?? self::DEFAULT_MODEL;
	}

	/**
	 * Generate a description for an image.
	 *
	 * @param string $image_path Absolute path to the image file.
	 *
	 * @return string|false The generated keywords or false on failure.
	 */
	final public function generate_description( string $image_path ): string|false {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		$api_key = $this->get_api_key();

		if ( false === $api_key ) {
			return false;
		}

		// Read and encode the image.
		if ( ! \file_exists( $image_path ) || ! \is_readable( $image_path ) ) {
			return false;
		}

		// Validate file size to prevent memory issues and DoS attacks.
		// Maximum file size: 10MB (10 * 1024 * 1024 bytes).
		$max_file_size = 10 * 1024 * 1024;

		/**
		 * Filter the maximum allowed file size for image processing.
		 * Pro can increase this limit for larger images.
		 *
		 * @param int    $max_file_size Maximum file size in bytes.
		 * @param string $image_path    Path to the image file.
		 *
		 * @since 1.0.0
		 */
		$max_file_size = \apply_filters( 'air_max_file_size', $max_file_size, $image_path );

		$file_size = \filesize( $image_path );

		if ( false === $file_size ) {
			return false;
		}

		if ( $file_size > $max_file_size ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( \sprintf( 'AI Image Renamer: Image file too large (%d bytes). Maximum allowed: %d bytes.',
				                      $file_size,
				                      $max_file_size ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}

		if ( 0 === $file_size ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: Image file is empty.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}

		$image_data = \file_get_contents( $image_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $image_data ) {
			return false;
		}

		$mime_type    = \mime_content_type( $image_path );
		$base64_image = \base64_encode( $image_data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$data_url     = \sprintf( 'data:%s;base64,%s', $mime_type, $base64_image );

		// Build the request payload.
		$payload = [
			'model'       => $this->get_model(),
			'temperature' => 1,
			'max_tokens'  => 100,
			'stream'      => false,
			'messages'    => [
				[
					'role'    => 'user',
					'content' => [
						[
							'type' => 'text',
							'text' => $this->get_prompt(),
						],
						[
							'type'      => 'image_url',
							'image_url' => [
								'url' => $data_url,
							],
						],
					],
				],
			],
		];

		/**
		 * Filter the API request payload before sending.
		 * Pro can modify model, temperature, max_tokens, or add parameters.
		 *
		 * @param array  $payload    The complete API payload.
		 * @param string $image_path Path to the image file.
		 *
		 * @since 1.0.0
		 */
		$payload = \apply_filters( 'air_api_payload', $payload, $image_path );

		$request_args = [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			],
			'body'    => \wp_json_encode( $payload ),
		];

		/**
		 * Filter the HTTP request arguments.
		 * Pro can modify timeout, headers, or other WP_Http args.
		 *
		 * @param array  $request_args The HTTP request arguments.
		 * @param array  $payload      The API payload.
		 * @param string $image_path   Path to the image file.
		 *
		 * @since 1.0.0
		 */
		$request_args = \apply_filters( 'air_api_request_args', $request_args, $payload, $image_path );

		// Make the API request.
		$response = \wp_remote_post( self::API_ENDPOINT, $request_args );

		if ( \is_wp_error( $response ) ) {
			return false;
		}

		$code = \wp_remote_retrieve_response_code( $response );
		$body = \wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			return false;
		}

		$decoded = json_decode( $body, true );

		/**
		 * Fires after the API response is received.
		 * Pro can use this for logging, analytics, or debugging.
		 *
		 * @param array|null $decoded    The decoded API response.
		 * @param int        $code       The HTTP response code.
		 * @param string     $image_path Path to the image file.
		 *
		 * @since 1.0.0
		 */
		\do_action( 'air_api_response', $decoded, $code, $image_path );

		if ( ! isset( $decoded['choices'][0]['message']['content'] ) ) {
			return false;
		}

		return \trim( $decoded['choices'][0]['message']['content'] );
	}
}
