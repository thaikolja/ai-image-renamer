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
 * Image Uploader Hook.
 *
 * @package AIR\Hooks
 */

declare( strict_types=1 );

namespace AIR\Hooks;

use AIR\Services\Groq_Service;
use AIR\Utils\File_Sanitizer;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Image_Uploader
 *
 * Hooks into WordPress upload process to rename images.
 */
class Image_Uploader {
	/**
	 * Groq service instance.
	 *
	 * @var Groq_Service
	 */
	private Groq_Service $groq_service;

	/**
	 * Transient prefix for storing pending alt text.
	 *
	 * @var string
	 */
	private const ALT_TEXT_TRANSIENT_PREFIX = 'air_pending_alt_text_';

	/**
	 * Transient expiration time in seconds (5 minutes).
	 *
	 * @var int
	 */
	private const TRANSIENT_EXPIRATION = 300;

	/**
	 * Constructor.
	 *
	 * @param Groq_Service $groq_service Groq service instance.
	 */
	public function __construct( Groq_Service $groq_service ) {
		$this->groq_service = $groq_service;
	}

	/**
	 * Initialize the upload hooks.
	 *
	 * @return void
	 */
	final public function init(): void {
		\add_filter( 'wp_handle_upload_prefilter', [ $this, 'process_upload' ] );
	}

	/**
	 * Process an uploaded file and rename it using AI.
	 *
	 * @param array $file The uploaded file data from $_FILES.
	 *
	 * @return array The modified file data.
	 */
	final public function process_upload( array $file ): array {
		// Check if the service is enabled.
		if ( ! $this->groq_service->is_enabled() ) {
			return $file;
		}

		// Check if there's an error with the upload.
		if ( ! empty( $file['error'] ) ) {
			return $file;
		}

		// Get the mime type.
		$mime_type = $file['type'] ?? '';

		// Check if this file type should be processed.
		if ( ! $this->groq_service->is_allowed_type( $mime_type ) ) {
			return $file;
		}

		// Get the temporary file path.
		$tmp_path = $file['tmp_name'] ?? '';

		if ( empty( $tmp_path ) || ! \file_exists( $tmp_path ) ) {
			return $file;
		}

		/**
		 * Filter whether to process this upload.
		 * Pro can use this to skip certain uploads or add conditions.
		 *
		 * @param bool   $should_process Whether to process this upload.
		 * @param array  $file           The file data.
		 * @param string $mime_type      The MIME type.
		 *
		 * @since 1.0.0
		 */
		if ( ! \apply_filters( 'air_should_process_upload', true, $file, $mime_type ) ) {
			return $file;
		}

		// Generate a description using the Groq API.
		$description = $this->groq_service->generate_description( $tmp_path );

		/**
		 * Filter the AI-generated description before processing.
		 * Pro can modify or enhance the description here.
		 *
		 * @param string|false $description The generated description or false.
		 * @param string       $tmp_path    Path to the temporary file.
		 * @param array        $file        The file data.
		 *
		 * @since 1.0.0
		 */
		$description = \apply_filters( 'air_generated_description', $description, $tmp_path, $file );

		// If generation failed, fall back to the original filename.
		if ( ! $description ) {
			return $file;
		}

		// Sanitize the generated description.
		$sanitized_name = File_Sanitizer::sanitize( $description );

		// Get the original extension.
		$original_name = $file['name'] ?? '';
		$extension     = File_Sanitizer::get_extension( $original_name );

		if ( empty( $extension ) ) {
			// Try to get extension from mime type.
			$extension = $this->get_extension_from_mime( $mime_type );
		}

		// Build the new filename.
		$new_filename = File_Sanitizer::build_filename( $sanitized_name, $extension );

		/**
		 * Filter the new filename before it is applied.
		 * Pro can modify the filename here (e.g., add prefixes, SKUs).
		 *
		 * @param string $new_filename   The new filename.
		 * @param string $sanitized_name The sanitized base name.
		 * @param string $extension      The file extension.
		 * @param array  $file           The original file data.
		 * @param string $description    The AI-generated description.
		 *
		 * @since 1.0.0
		 */
		$new_filename = \apply_filters( 'air_new_filename', $new_filename, $sanitized_name, $extension, $file, $description );

		$new_filename = \sanitize_file_name( (string) $new_filename );

		if ( empty( $new_filename ) || '.' === $new_filename ) {
			$new_filename = File_Sanitizer::build_filename( $sanitized_name, $extension );
		}

		// Ensure the final filename is lowercase (defensive check).
		$new_filename = strtolower( $new_filename );

		// Store original name for action hook.
		$original_name = $file['name'] ?? '';

		// Update the file array with the new name.
		$file['name'] = $new_filename;

		/**
		 * Fires after an image has been renamed.
		 * Pro can use this to log renames, update history, etc.
		 *
		 * @param string $new_filename  The new filename.
		 * @param string $original_name The original filename.
		 * @param string $description   The AI-generated description.
		 * @param array  $file          The file data.
		 *
		 * @since 1.0.0
		 */
		\do_action( 'air_image_renamed', $new_filename, $original_name, $description, $file );

		// Check if Alt Text feature is enabled.
		$options = \get_option( 'air_options', [] );
		$set_alt = isset( $options['set_alt_text'] ) && '1' === (string) $options['set_alt_text'];

		if ( $set_alt && ! empty( $description ) ) {
			$clean_text = ucfirst( str_replace( [ '-', '_' ], ' ', $sanitized_name ) );

			/**
			 * Filter the alt text before it is saved.
			 * Pro can enhance alt text (e.g., full sentences, translations).
			 *
			 * @param string $clean_text  The alt text to save.
			 * @param string $description The AI-generated description.
			 * @param array  $file        The file data.
			 *
			 * @since 1.0.0
			 */
			$alt_text = \apply_filters( 'air_alt_text', $clean_text, $description, $file );
			$alt_text = \sanitize_text_field( \wp_strip_all_tags( (string) $alt_text ) );

			if ( '' === $alt_text ) {
				return $file;
			}

			// Store alt text in transient using the new filename as key.
			// This prevents race conditions when multiple images are uploaded simultaneously.
			$transient_key = self::ALT_TEXT_TRANSIENT_PREFIX . str_replace( '-', '', \wp_generate_uuid4() );
			\set_transient( $transient_key, $alt_text, self::TRANSIENT_EXPIRATION );

			// Hook into attachment creation to save this.
			\add_action( 'add_attachment', function ( $post_id ) use ( $transient_key ) {
				$this->set_image_alt_text( $post_id, $transient_key );
			} );
		}

		return $file;
	}

	/**
	 * Set the image Alt Text meta.
	 *
	 * @param int    $post_id       Attachment ID.
	 * @param string $transient_key The transient key containing the alt text.
	 *
	 * @return void
	 */
	final public function set_image_alt_text( int $post_id, string $transient_key ): void {
		$alt_text = \get_transient( $transient_key );

		if ( ! empty( $alt_text ) ) {
			\update_post_meta( $post_id, '_wp_attachment_image_alt', $alt_text );
			\update_post_meta( $post_id, '_ai_image_renamer_alt_set', 'true' );

			// Delete the transient after use to prevent memory leaks.
			\delete_transient( $transient_key );
		}
	}

	/**
	 * Get file extension from mime type.
	 *
	 * @param string $mime_type The mime type.
	 *
	 * @return string The file extension.
	 */
	private function get_extension_from_mime( string $mime_type ): string {
		$mime_to_ext = [
			'image/jpeg' => 'jpg',
			'image/png'  => 'png',
			'image/webp' => 'webp',
			'image/gif'  => 'gif',
		];

		$extension = $mime_to_ext[ $mime_type ] ?? 'jpg';

		$extension = \apply_filters( 'air_mime_to_ext', $extension, $mime_type );

		return is_string( $extension ) && '' !== $extension ? strtolower( $extension ) : 'jpg';
	}
}
