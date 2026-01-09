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
 */
class Plugin {


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
	 * @var Groq_Service|null
	 */
	private Groq_Service $groq_service;

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	final public function init(): void {
		// Initialize services.
		$this->encryption_service = new Encryption_Service();
		$this->template_engine    = new Template_Engine();
		$this->groq_service       = new Groq_Service( $this->encryption_service );

		// Initialize admin settings page.
		if ( \is_admin() ) {
			$settings_page = new Settings_Page( $this->template_engine, $this->encryption_service, $this->groq_service );
			$settings_page->init();
		}

		// Initialize upload hook.
		$image_uploader = new Image_Uploader( $this->groq_service );
		$image_uploader->init();
	}
}
