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
 * Uninstall Script.
 *
 * Removes all plugin data when the plugin is deleted via WordPress admin.
 * This file is called automatically by WordPress when the plugin is uninstalled.
 *
 * @package AIR
 */

// Security check: exit if not called by WordPress.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Clean up all plugin options from the database.
 */
function air_uninstall_cleanup(): void
{
    // Delete plugin options.
    delete_option('air_options');
    delete_option('air_encryption_key');

    // Delete user meta for dismissed notices (for all users).
    global $wpdb;

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->delete(
        $wpdb->usermeta,
        array('meta_key' => 'air_encryption_notice_dismissed'),
        array('%s')
    );

    // Clear any transients the plugin may have set.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_air_pending_alt_text_%'
        )
    );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_air_pending_alt_text_%'
        )
    );

    // Delete rate limiter transients.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_air_%'
        )
    );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            '_transient_timeout_air_%'
        )
    );

    // Remove Twig cache directory.
    $cache_dir = plugin_dir_path(__FILE__) . 'cache/twig';
    if (is_dir($cache_dir)) {
        air_remove_directory_recursive($cache_dir);
    }

    // Remove parent cache directory if empty.
    $parent_cache_dir = plugin_dir_path(__FILE__) . 'cache';
    if (is_dir($parent_cache_dir)) {
        $files = @scandir($parent_cache_dir);
        if (is_array($files) && count($files) <= 2) { // Only . and ..
            @rmdir($parent_cache_dir);
        }
    }
}

/**
 * Recursively remove a directory and its contents.
 *
 * @param string $dir The directory path to remove.
 *
 * @return bool True on success, false on failure.
 */
function air_remove_directory_recursive(string $dir): bool
{
    if (! is_dir($dir)) {
        return false;
    }

    $files = @scandir($dir);
    if (false === $files) {
        return false;
    }

    foreach ($files as $file) {
        if ('.' === $file || '..' === $file) {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if (is_dir($path)) {
            air_remove_directory_recursive($path);
        } else {
            @unlink($path);
        }
    }

    return @rmdir($dir);
}

// Run the cleanup.
air_uninstall_cleanup();
