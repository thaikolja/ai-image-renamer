<?php
/**
 * AI Image Renamer.
 *
 * @description    Uses AI to rename images during upload for SEO-friendly filenames.
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
 * SVG Sanitizer Utility.
 *
 * @package AIR\Utils
 */

declare(strict_types=1);

namespace AIR\Utils;

/**
 * Class SVG_Sanitizer
 *
 * Validates and sanitizes SVG content to prevent XSS attacks.
 */
class SVG_Sanitizer {



	/**
	 * Allowed SVG elements for sprite icons.
	 *
	 * @var array
	 */
	private const ALLOWED_ELEMENTS
	= array(
		'svg',
		'symbol',
		'path',
		'circle',
		'rect',
		'polyline',
		'polygon',
		'line',
		'ellipse',
	);

	/**
	 * Allowed SVG attributes.
	 *
	 * @var array
	 */
	private const ALLOWED_ATTRIBUTES
	= array(
		'id',
		'viewBox',
		'xmlns',
		'display',
		'd',
		'cx',
		'cy',
		'r',
		'rx',
		'ry',
		'x',
		'y',
		'width',
		'height',
		'fill',
		'stroke',
		'stroke-width',
		'points',
		'x1',
		'y1',
		'x2',
		'y2',
	);

	/**
	 * Validate and sanitize SVG content.
	 *
	 * @param  string $svg_content  The raw SVG content.
	 *
	 * @return string|false Sanitized SVG content or false if invalid.
	 */
	public static function sanitize( string $svg_content ): string|false {
		if ( empty( $svg_content ) ) {
			return false;
		}

		// Check for potentially dangerous content before parsing.
		if ( self::contains_dangerous_content( $svg_content ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: SVG contains dangerous content and was rejected.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}

		// Parse the SVG using DOMDocument.
		$dom = new \DOMDocument();

		// Suppress warnings for malformed XML.
		\libxml_use_internal_errors( true );

		$loaded = $dom->loadXML( $svg_content, \LIBXML_NOERROR | \LIBXML_NOWARNING );

		\libxml_clear_errors();

		if ( ! $loaded ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: Failed to parse SVG content.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}

		// Validate the SVG structure.
		if ( ! self::validate_svg_structure( $dom ) ) {
			return false;
		}

		// Sanitize the DOM.
		if ( ! self::sanitize_dom( $dom ) ) {
			return false;
		}

		// Return the sanitized SVG.
		$sanitized = $dom->saveXML();

		if ( false === $sanitized ) {
			return false;
		}

		return $sanitized;
	}

	/**
	 * Check for potentially dangerous content in SVG.
	 *
	 * @param  string $content  The SVG content.
	 *
	 * @return bool True if dangerous content is found.
	 */
	private static function contains_dangerous_content( string $content ): bool {
		$dangerous_patterns = array(
			// Script tags and event handlers.
			'<script',
			'onload',
			'onerror',
			'onclick',
			'onmouseover',
			'javascript:',
			'data:text/html',
			'data:application',
			// External references.
			'xlink:href',
			'href=',
			// Style tags with potential CSS injection.
			'<style',
			// Embedded objects.
			'<embed',
			'<object',
			'<iframe',
			// ForeignObject (can contain HTML).
			'<foreignObject',
			// Entity references (can be used for XXE).
			'<!ENTITY',
		);

		$content_lower = strtolower( $content );

		foreach ( $dangerous_patterns as $pattern ) {
			if ( str_contains( $content_lower, strtolower( $pattern ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Validate the SVG structure.
	 *
	 * @param  \DOMDocument $dom  The parsed DOM document.
	 *
	 * @return bool True if valid.
	 */
	private static function validate_svg_structure( \DOMDocument $dom ): bool {
		$svg_elements = $dom->getElementsByTagName( 'svg' );

		if ( 0 === $svg_elements->length ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: SVG has no root svg element.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}

		// Check for proper namespace.
		$root = $svg_elements->item( 0 );
		if ( ! $root ) {
			return false;
		}

		$namespace = $root->namespaceURI;
		if ( 'http://www.w3.org/2000/svg' !== $namespace ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: SVG has invalid namespace: ' . ( $namespace ?: 'none' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}

		return true;
	}

	/**
	 * Sanitize the DOM by removing disallowed elements and attributes.
	 *
	 * @param  \DOMDocument $dom  The DOM document to sanitize.
	 *
	 * @return bool True if sanitization succeeded.
	 */
	private static function sanitize_dom( \DOMDocument $dom ): bool {
		$xpath = new \DOMXPath( $dom );
		$xpath->registerNamespace( 'svg', 'http://www.w3.org/2000/svg' );

		// Remove disallowed elements.
		$all_elements       = $dom->getElementsByTagName( '*' );
		$elements_to_remove = array();

		foreach ( $all_elements as $element ) {
			$local_name = $element->localName;

			if ( ! in_array( $local_name, self::ALLOWED_ELEMENTS, true ) ) {
				$elements_to_remove[] = $element;
				continue;
			}

			// Remove disallowed attributes.
			$attributes_to_remove = array();
			foreach ( $element->attributes as $attr ) {
				if ( ! in_array( $attr->name, self::ALLOWED_ATTRIBUTES, true ) ) {
					$attributes_to_remove[] = $attr;
				}
			}

			foreach ( $attributes_to_remove as $attr ) {
				$element->removeAttributeNode( $attr );
			}
		}

		// Remove elements after iteration.
		foreach ( $elements_to_remove as $element ) {
			if ( $element->parentNode ) {
				$element->parentNode->removeChild( $element );
			}
		}

		return true;
	}

	/**
	 * Load and sanitize SVG from a file.
	 *
	 * @param  string $file_path  The path to the SVG file.
	 *
	 * @return string|false Sanitized SVG content or false if invalid.
	 */
	public static function load_and_sanitize_file( string $file_path ): string|false {
		// Validate file path is within plugin directory.
		$real_path  = \realpath( $file_path );
		$plugin_dir = \realpath( AIR_PLUGIN_DIR );

		if ( false === $real_path || false === $plugin_dir ) {
			return false;
		}

		if ( 0 !== \strpos( $real_path, $plugin_dir ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: Attempted to load SVG from outside plugin directory.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}

		// Check file exists and is readable.
		if ( ! \file_exists( $real_path ) || ! \is_readable( $real_path ) ) {
			return false;
		}

		// Check file extension.
		$path_info = \pathinfo( $real_path );
		if ( 'svg' !== strtolower( $path_info['extension'] ?? '' ) ) {
			return false;
		}

		// Read file content.
		$content = \file_get_contents( $real_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file read.
		if ( false === $content ) {
			return false;
		}

		// Sanitize the content.
		return self::sanitize( $content );
	}
}
