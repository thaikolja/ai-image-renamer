<?php
/*
 * @name:           AI Image Renamer
 * @wordpress       Uses AI to rename images during upload for SEO-friendly filenames.
 * @author          Kolja Nolte <kolja.nolte@gmail.com>
 * @copyright       2025-2026 (C) Kolja Nolte
 * @see             https://docs.kolja-nolte.com/wp-ai-image-renamer/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General final public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Released under the GNU General final public License v2 or later.
 * See: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package AIR
 * @license GPL-2.0-or-later
 */


/**
 * Main Plugin Bootstrap Class.
 *
 * @package AIR
 */

declare( strict_types=1 );

namespace AIR;

use AIR\Admin\Settings_Page;
use AIR\Hooks\Image_Uploader;
use AIR\Services\Encryption_Service;
use AIR\Services\Groq_Service;
use AIR\Services\Template_Engine;

/**
 * Class Plugin
 *
 * Bootstraps all plugin components.
 * Provides extension points for Pro add-on via hooks and final public getters.
 */
class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Template engine instance.
	 *
	 * @var Template_Engine|null
	 */
	private Template_Engine $template_engine;

	/**
	 * Encryption service instance.
	 *
	 * @var Encryption_Service|null
	 */
	private Encryption_Service $encryption_service;

	/**
	 * Groq API service instance.
	 *
	 * @var Groq_Service
	 */
	private Groq_Service $groq_service;

	/**
	 * Settings page instance.
	 *
	 * @var Settings_Page|null
	 */
	private ?Settings_Page $settings_page = null;

	/**
	 * Image uploader instance.
	 *
	 * @var Image_Uploader|null
	 */
	private ?Image_Uploader $image_uploader = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init(): void {
		// Store instance for singleton access.
		self::$instance = $this;

		// Initialize services.
		$this->encryption_service = new Encryption_Service();
		$this->template_engine    = new Template_Engine();
		$this->groq_service       = new Groq_Service( $this->encryption_service );

		/**
		 * Fires after core services are initialized but before components.
		 * Pro plugin can hook here to modify services or add its own.
		 *
		 * @param  Plugin  $plugin  The plugin instance.
		 *
		 * @since 1.0.0
		 */
		\do_action( 'air_services_loaded', $this );

		// Initialize admin settings page.
		if ( \is_admin() ) {
			$this->settings_page = new Settings_Page( $this->template_engine, $this->encryption_service, $this->groq_service );
			$this->settings_page->init();
		}

		// Initialize upload hook.
		$this->image_uploader = new Image_Uploader( $this->groq_service );
		$this->image_uploader->init();

		/**
		 * Fires after the plugin is fully initialized.
		 * Pro plugin should hook here to add its features.
		 *
		 * @param  Plugin  $plugin  The plugin instance.
		 *
		 * @since 1.0.0
		 */
		\do_action( 'air_loaded', $this );
	}

	/**
	 * Get the encryption service.
	 *
	 * @return Encryption_Service
	 */
	final public function get_encryption_service(): Encryption_Service {
		return $this->encryption_service;
	}

	/**
	 * Get the template engine.
	 *
	 * @return Template_Engine
	 */
	final public function get_template_engine(): Template_Engine {
		return $this->template_engine;
	}

	/**
	 * Get the Groq service.
	 *
	 * @return Groq_Service
	 */
	final public function get_groq_service(): Groq_Service {
		return $this->groq_service;
	}

	/**
	 * Get the settings page instance.
	 *
	 * @return Settings_Page|null
	 */
	final public function get_settings_page(): ?Settings_Page {
		return $this->settings_page;
	}

	/**
	 * Get the image uploader instance.
	 *
	 * @return Image_Uploader|null
	 */
	final public function get_image_uploader(): ?Image_Uploader {
		return $this->image_uploader;
	}

	/**
	 * Check if Pro add-on is active.
	 *
	 * @return bool
	 */
	final public function is_pro_active(): bool {
		return \class_exists( 'AIR_Pro\\Pro' ) && \apply_filters( 'air_pro_is_licensed', false );
	}
}
