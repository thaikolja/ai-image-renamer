<?php
/*
 * @name:           AI Image Renamer
 * @wordpress       Uses AI to rename images during upload for SEO-friendly filenames.
 * @author          Kolja Nolte <kolja.nolte@gmail.com>
 * @copyright       2025-2026 (C) Kolja Nolte
 * @see             https://docs.kolja-nolte.com/wp-ai-image-renamer/
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
 * API Key Validator Utility.
 *
 * @package AIR\Utils
 */

declare( strict_types=1 );

namespace AIR\Utils;

/**
 * Class API_Key_Validator
 *
 * Validates API key format and structure.
 */
class API_Key_Validator {

	/**
	 * Groq API key prefix.
	 *
	 * @var string
	 */
	private const GROQ_KEY_PREFIX = 'gsk_';

	/**
	 * Minimum API key length.
	 *
	 * @var int
	 */
	private const MIN_LENGTH = 32;

	/**
	 * Maximum API key length.
	 *
	 * @var int
	 */
	private const MAX_LENGTH = 128;

	/**
	 * Validate a Groq API key format.
	 *
	 * @param  string  $api_key  The API key to validate.
	 *
	 * @return array Validation result with 'valid' (bool) and 'message' (string) keys.
	 */
	public static function validate_groq_key( string $api_key ): array {
		// Trim whitespace.
		$api_key = trim( $api_key );

		// Check if empty.
		if ( empty( $api_key ) ) {
			return [
				'valid'   => false,
				'message' => \__( 'API key cannot be empty.', 'ai-image-renamer' ),
			];
		}

		// Check prefix.
		if ( ! str_starts_with( $api_key, self::GROQ_KEY_PREFIX ) ) {
			return [
				'valid'   => false,
				'message' => \sprintf( /* translators: %s: API key prefix */ \__( 'Invalid API key format. Groq API keys start with <code>%s</code>', 'ai-image-renamer' ), self::GROQ_KEY_PREFIX ),
			];
		}

		// Check length.
		$key_length = strlen( $api_key );
		if ( $key_length < self::MIN_LENGTH ) {
			return [
				'valid'   => false,
				'message' => \sprintf( /* translators: %d: Minimum length */ \__( 'The API key is too short. It must be at least %d characters long.', 'ai-image-renamer' ), self::MIN_LENGTH ),
			];
		}

		if ( $key_length > self::MAX_LENGTH ) {
			return [
				'valid'   => false,
				'message' => \sprintf( /* translators: %d: Maximum length */ \__( 'The API key is too long. It must be at most %d characters long.', 'ai-image-renamer' ), self::MAX_LENGTH ),
			];
		}

		// Check for valid characters (alphanumeric, underscore, hyphen).
		$pattern = '/^' . preg_quote( self::GROQ_KEY_PREFIX, '/' ) . '[a-zA-Z0-9_-]+$/';

		if ( ! preg_match( $pattern, $api_key ) ) {
			return [
				'valid'   => false,
				'message' => \__( 'The API key contains invalid characters. <strong>Only alphanumeric characters, hyphens, and underscores</strong> are allowed.', 'ai-image-renamer' ),
			];
		}

		// Check for suspicious patterns that might indicate injection attempts.
		if ( self::contains_suspicious_patterns( $api_key ) ) {
			return [
				'valid'   => false,
				'message' => \__( 'The API key contains invalid patterns.', 'ai-image-renamer' ),
			];
		}

		return [
			'valid'   => true,
			'message' => '',
		];
	}

	/**
	 * Check if the API key contains suspicious patterns.
	 *
	 * @param  string  $api_key  The API key to check.
	 *
	 * @return bool True if suspicious patterns are found.
	 */
	private static function contains_suspicious_patterns( string $api_key ): bool {
		$suspicious_patterns = [
			// SQL injection patterns
			'--',
			'/*',
			'*/',
			';',
			'\'',
			'"',
			'OR',
			'AND',
			'UNION',
			'SELECT',
			'DROP',
			'INSERT',
			'UPDATE',
			'DELETE',
			'WHERE',
			// XSS patterns
			'<script',
			'onload',
			'onerror',
			'javascript:',
			// Path traversal
			'../',
			'..\\',
			'%2e%2e',
		];

		$key_upper = strtoupper( $api_key );

		foreach ( $suspicious_patterns as $pattern ) {
			if ( str_contains( $key_upper, strtoupper( $pattern ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Sanitize an API key for storage.
	 *
	 * Note: This should only be used for display purposes, not for actual API calls.
	 * For actual API calls, use the raw key.
	 *
	 * @param  string  $api_key  The API key to sanitize.
	 *
	 * @return string Sanitized key (masked for display).
	 */
	public static function mask_for_display( string $api_key ): string {
		if ( empty( $api_key ) ) {
			return '';
		}

		// Show first 4 characters after prefix, then mask the rest.
		$prefix_len  = strlen( self::GROQ_KEY_PREFIX );
		$visible_len = 4;

		if ( strlen( $api_key ) <= $prefix_len + $visible_len ) {
			// Key is too short to mask properly.
			return str_repeat( '•', strlen( $api_key ) );
		}

		$visible = substr( $api_key, 0, $prefix_len + $visible_len );
		$masked  = str_repeat( '•', strlen( $api_key ) - $prefix_len - $visible_len );

		return $visible . $masked;
	}

	/**
	 * Check if an API key is masked (for display purposes).
	 *
	 * @param  string  $api_key  The API key to check.
	 *
	 * @return bool True if the key is masked.
	 */
	public static function is_masked( string $api_key ): bool {
		return str_contains( $api_key, '•' );
	}
}
