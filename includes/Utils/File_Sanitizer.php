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
 * File Sanitizer Utility.
 *
 * @package AIR\Utils
 */

declare( strict_types=1 );

namespace AIR\Utils;

/**
 * Class File_Sanitizer
 *
 * Sanitizes filenames according to SEO-friendly rules.
 */
class File_Sanitizer {
	/**
	 * Sanitize a filename.
	 *
	 * Rules:
	 * - Convert to lowercase
	 * - Replace spaces and underscores with dashes
	 * - Keep only alphanumeric characters and dashes
	 * - Remove consecutive dashes
	 * - Trim dashes from start and end
	 *
	 * @param string $filename The raw filename (without extension).
	 *
	 * @return string The sanitized filename.
	 */
	public static function sanitize( string $filename ): string {
		// Convert to lowercase.
		$sanitized = strtolower( $filename );

		// Replace spaces and underscores with dashes.
		$sanitized = str_replace( [ ' ', '_' ], '-', $sanitized );

		// Remove any character that is not alphanumeric or a dash.
		$sanitized = preg_replace( '/[^a-z0-9\-]/', '', $sanitized );

		// Replace multiple consecutive dashes with a single dash.
		$sanitized = preg_replace( '/-+/', '-', $sanitized );

		// Trim dashes from the beginning and end.
		$sanitized = \trim( $sanitized, '-' );

		// Ensure the filename is not empty.
		if ( empty( $sanitized ) ) {
			$sanitized = 'image';
		}

		return $sanitized;
	}

	/**
	 * Build a complete filename with extension.
	 *
	 * @param string $basename  The sanitized base name.
	 * @param string $extension The file extension (without dot).
	 *
	 * @return string The complete filename.
	 */
	public static function build_filename( string $basename, string $extension ): string {
		return $basename . '.' . strtolower( $extension );
	}

	/**
	 * Extract the extension from a filename.
	 *
	 * @param string $filename The complete filename.
	 *
	 * @return string The extension (without dot), lowercase.
	 */
	public static function get_extension( string $filename ): string {
		$pathinfo = pathinfo( $filename );

		return strtolower( $pathinfo['extension'] ?? '' );
	}
}
