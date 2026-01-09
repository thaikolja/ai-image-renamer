<?php

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
 *
 * @package AIR
 */

declare( strict_types=1 );


// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIR\Plugin;

// Define plugin constants.
const AIR_VERSION = '1.0.0';

define( 'AIR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AIR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load Composer autoloader.
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

// Bootstrap the plugin.
add_action( 'plugins_loaded', function () {
	$plugin = new Plugin();
	$plugin->init();
} );
