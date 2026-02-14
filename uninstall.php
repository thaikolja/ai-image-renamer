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
 * Uninstall Script.
 *
 * Removes all plugin data when the plugin is deleted via WordPress admin.
 * This file is called automatically by WordPress when the plugin is uninstalled.
 *
 * @package AIR
 */

// Security check: exit if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean up all plugin options from the database.
 */
function air_uninstall_cleanup(): void {
	// Initialize WP_Filesystem.
	global $wp_filesystem;
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}

	WP_Filesystem();

	// Delete plugin options.
	delete_option( 'air_options' );
	delete_option( 'air_encryption_key' );

	// Delete user meta for dismissed notices (for all users).
	delete_metadata( 'user', 0, 'air_encryption_notice_dismissed', '', true );

	// Transients (air_pending_alt_text_*, air_rate_limit_*) are short-lived
	// (≤5 min TTL) and auto-expire. No explicit cleanup needed.

	// Remove Twig cache directory.
	$cache_dir = plugin_dir_path( __FILE__ ) . 'cache/twig';

	if ( is_dir( $cache_dir ) ) {
		air_remove_directory_recursive( $cache_dir );
	}

	// Remove parent cache directory if empty.
	$parent_cache_dir = plugin_dir_path( __FILE__ ) . 'cache';

	if ( is_dir( $parent_cache_dir ) && $wp_filesystem ) {
		$dirlist = $wp_filesystem->dirlist( $parent_cache_dir );
		if ( is_array( $dirlist ) && 0 === count( $dirlist ) ) {
			$wp_filesystem->rmdir( $parent_cache_dir );
		}
	}
}

/**
 * Recursively remove a directory and its contents.
 *
 * @param  string $dir  The directory path to remove.
 *
 * @return bool True on success, false on failure.
 */
function air_remove_directory_recursive( string $dir ): bool {
	global $wp_filesystem;

	if ( ! $wp_filesystem || ! is_dir( $dir ) ) {
		return false;
	}

	$dirlist = $wp_filesystem->dirlist( $dir );
	if ( false === $dirlist ) {
		return false;
	}

	foreach ( $dirlist as $filename => $fileinfo ) {
		$path = trailingslashit( $dir ) . $filename;

		if ( 'd' === $fileinfo['type'] ) {
			air_remove_directory_recursive( $path );
		} else {
			wp_delete_file( $path );
		}
	}

	return $wp_filesystem->rmdir( $dir );
}

// Run the cleanup.
air_uninstall_cleanup();
