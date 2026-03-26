<?php
/**
 * AI Image Renamer.
 *
 * @description     Uses AI to rename images during upload for SEO-friendly filenames.
 * @author          Kolja Nolte <kolja.nolte@gmail.com>
 * @copyright       2025-2026 (C) Kolja Nolte
 * @see             https://docs.kolja-nolte.com/ai-image-renamer/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Released under the GNU General Public License v2 or later.
 * See: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package         AIR
 * @license         GPL-2.0-or-later
 */

/**
 * Plugin Name:       AI Image Renamer
 * Plugin URI:        https://docs.kolja-nolte.com/ai-image-renamer
 * Description:       Automatically renames uploaded images using Groq's Vision API for SEO-friendly filenames.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            Kolja Nolte
 * Author URI:        https://www.kolja-nolte.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-image-renamer
 * Domain Path:       /languages
 * Donate Link:       https://www.paypal.com/paypalme/thaikolja/10/
 *
 * @package AIR
 */

declare( strict_types=1 );

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIR\Plugin;

const AIR_VERSION = '1.0.0';

define( 'AIR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AIR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( file_exists( AIR_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
	require_once AIR_PLUGIN_DIR . 'vendor/autoload.php';
} else {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>';
		esc_html_e( 'AI Image Renamer: Composer dependencies not installed. Please run "composer install" in the plugin directory.', 'ai-image-renamer' );
		echo '</p></div>';
	} );

	return;
}

/**
 * Loads the plugin's text domain for translation.
 *
 * This method makes the plugin's translation files available
 * for internationalization and localization, enabling text strings
 * within the plugin to be displayed in different languages.
 *
 * @return void
 */
add_action( 'init', function (): void {
	load_plugin_textdomain( 'ai-image-renamer', false, dirname( AIR_PLUGIN_BASENAME ) . '/languages' );
} );

/**
 * Initializes and executes the Plugin object.
 *
 * This function creates a new instance of the Plugin class,
 * and subsequently calls its init() method to initialize it.
 *
 * @return void
 */
add_action( 'plugins_loaded', function () {
	$plugin = new Plugin();
	$plugin->init();
} );
