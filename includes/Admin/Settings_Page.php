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
 * Admin Settings Page.
 *
 * @package AIR\Admin
 */

declare( strict_types=1 );

namespace AIR\Admin;

use AIR\Utils\Rate_Limiter;
use AIR\Utils\SVG_Sanitizer;
use AIR\Services\Groq_Service;
use AIR\Utils\API_Key_Validator;
use AIR\Services\Template_Engine;
use AIR\Services\Encryption_Service;

/**
 * Class Settings_Page
 *
 * Handles the plugin settings page under Settings menu.
 */
class Settings_Page {


    /**
     * Settings page slug.
     *
     * @var string
     */
    public const PAGE_SLUG = 'ai-image-renamer';

    /**
     * Option group name.
     *
     * @var string
     */
    public const OPTION_GROUP = 'air_settings';

    /**
     * Option name for storing settings.
     *
     * @var string
     */
    public const OPTION_NAME = 'air_options';

    /**
     * Template engine instance.
     *
     * @var Template_Engine
     */
    private Template_Engine $template_engine;

    /**
     * Encryption service instance.
     *
     * @var Encryption_Service
     */
    private Encryption_Service $encryption_service;

    /**
     * Groq service instance.
     *
     * @var Groq_Service
     */
    private Groq_Service $groq_service;

    /**
     * Constructor.
     *
     * @param  Template_Engine     $template_engine     Template engine instance.
     * @param  Encryption_Service  $encryption_service  Encryption service instance.
     * @param  Groq_Service        $groq_service        Groq service instance.
     */
    public function __construct(
        Template_Engine $template_engine, Encryption_Service $encryption_service, Groq_Service $groq_service
    ) {
        $this->template_engine    = $template_engine;
        $this->encryption_service = $encryption_service;
        $this->groq_service       = $groq_service;
    }

    /**
     * Initialize the settings page.
     *
     * @return void
     */
    final public function init(): void {
        \add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        \add_action( 'admin_init', [ $this, 'register_settings' ] );
        \add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        \add_action( 'admin_notices', [ $this, 'show_encryption_notice' ] );
        \add_action( 'wp_ajax_air_test_connection', [ $this, 'ajax_test_connection' ] );
        \add_action( 'wp_ajax_air_delete_api_key', [ $this, 'ajax_delete_api_key' ] );
        \add_action( 'wp_ajax_air_dismiss_encryption_notice', [ $this, 'ajax_dismiss_encryption_notice' ] );
    }

    /**
     * Add the settings page to the admin menu.
     *
     * @return void
     */
    final public function add_settings_page(): void {
        \add_submenu_page( 'upload.php', 'AI Image Renamer', 'AI Image Renamer', 'manage_options', self::PAGE_SLUG, [
            $this,
            'render_settings_page',
        ] );
    }

    /**
     * Register plugin settings.
     *
     * @return void
     */
    final public function register_settings(): void {
        \register_setting( self::OPTION_GROUP, self::OPTION_NAME, [
            'type'              => 'array',
            'sanitize_callback' => [ $this, 'sanitize_settings' ],
            'default'           => $this->get_defaults(),
        ] );

        // Main settings section.
        \add_settings_section( 'air_main_section', '<span class="dashicons dashicons-admin-settings"></span> ' . \__( 'API Configuration', 'ai-image-renamer' ), function () {
            echo $this->template_engine->render( 'admin/sections/hero.twig' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }, self::PAGE_SLUG );

        // Enable/Disable toggle.
        \add_settings_field( 'enabled', '<label for="air_enabled"><span class="dashicons dashicons-lightbulb"></span> ' . \__( 'Enable Auto-Rename', 'ai-image-renamer' ) . '</label>', [
            $this,
            'render_enabled_field',
        ], self::PAGE_SLUG, 'air_main_section' );

        // API Key field.
        \add_settings_field( 'api_key', '<label for="air_api_key"><span class="dashicons dashicons-admin-network"></span> ' . \__( 'Groq API Key', 'ai-image-renamer' ) . '</label>', [
            $this,
            'render_api_key_field',
        ], self::PAGE_SLUG, 'air_main_section' );

        // Model Selection.
        \add_settings_field( 'model', '<label for="air_model"><span class="dashicons dashicons-products"></span> ' . \__( 'AI Model', 'ai-image-renamer' ) . '</label>', [
            $this,
            'render_model_field',
        ], self::PAGE_SLUG, 'air_main_section' );

        // Alt text toggle.
        \add_settings_field( 'set_alt_text', '<label for="air_set_alt_text"><span class="dashicons dashicons-text"></span> ' . \__( 'Use as <code>alt=""</code>', 'ai-image-renamer' ) . '</label>', [
            $this,
            'render_alt_text_field',
        ], self::PAGE_SLUG, 'air_main_section' );

        // File types section.
        \add_settings_section( 'air_file_types_section', '<span class="dashicons dashicons-format-image"></span> ' . \__( 'File Types', 'ai-image-renamer' ), function () {
            echo '<p>' . \esc_html__( 'Select which image types to process.', 'ai-image-renamer' ) . '</p>';
        }, self::PAGE_SLUG );

        \add_settings_field( 'file_types', '<span class="dashicons dashicons-images-alt2"></span> ' . \__( 'Allowed Types', 'ai-image-renamer' ), [
            $this,
            'render_file_types_field',
        ], self::PAGE_SLUG, 'air_file_types_section' );

        // Advanced section.
        \add_settings_section( 'air_advanced_section', '<span class="dashicons dashicons-admin-tools"></span> ' . \__( 'Advanced Settings', 'ai-image-renamer' ), function () {
            echo '<p>' . \esc_html__( 'Customize the AI prompt and keyword settings.', 'ai-image-renamer' ) . '</p>';
        }, self::PAGE_SLUG );

        \add_settings_field( 'max_keywords', '<label for="air_max_keywords"><span class="dashicons dashicons-editor-ol"></span> ' . \__( 'Max Keywords', 'ai-image-renamer' ) . '</label>', [
            $this,
            'render_max_keywords_field',
        ], self::PAGE_SLUG, 'air_advanced_section' );

        /**
         * Fires after all core settings fields are registered.
         * Pro can add additional sections and fields here.
         *
         * @param  string  $page_slug  The settings page slug.
         *
         * @since 1.0.0
         */
        \do_action( 'air_register_settings_fields', self::PAGE_SLUG );
    }

    /**
     * Get default settings.
     *
     * @return array Default settings.
     */
    private function get_defaults(): array {
        $defaults = [
            'api_key'      => '',
            'enabled'      => true,
            'file_types'   => [ 'image/jpeg', 'image/png', 'image/webp', 'image/gif' ],
            'max_keywords' => 5,
            'set_alt_text' => false,
            'model'        => 'meta-llama/llama-4-maverick-17b-128e-instruct',
        ];

        /**
         * Filter the default settings values.
         * Pro can add its own defaults here.
         *
         * @param  array  $defaults  The default settings.
         *
         * @since 1.0.0
         */
        return \apply_filters( 'air_settings_defaults', $defaults );
    }

    /**
     * Sanitize settings before saving.
     *
     * @param  array  $input  The input settings.
     *
     * @return array Sanitized settings.
     */
    final public function sanitize_settings( array $input ): array {
        $sanitized = $this->get_defaults();
        $old       = \get_option( self::OPTION_NAME, $this->get_defaults() );

        // Helper to decrypt if needed, though we deal with input here.
        // We expect plaintext from the admin form since we display plaintext now.

        if ( isset( $input[ 'api_key' ] ) ) {
            $plaintext = \trim( $input[ 'api_key' ] );

            if ( empty( $plaintext ) ) {
                $sanitized[ 'api_key' ] = '';
            } elseif ( API_Key_Validator::is_masked( $plaintext ) ) {
                // If the key is masked (user didn't change it), keep the old encrypted key.
                $sanitized[ 'api_key' ] = $old[ 'api_key' ] ?? '';
            } else {
                // Use strict API key validation.
                $validation = API_Key_Validator::validate_groq_key( $plaintext );

                if ( ! $validation[ 'valid' ] ) {
                    \add_settings_error( self::OPTION_NAME, 'invalid_key', $validation[ 'message' ] );
                    // Keep old key if validation failed.
                    $sanitized[ 'api_key' ] = $old[ 'api_key' ] ?? '';
                } else {
                    $encrypted = $this->encryption_service->encrypt( $plaintext );
                    if ( false !== $encrypted ) {
                        $sanitized[ 'api_key' ] = $encrypted;
                    } else {
                        \add_settings_error( self::OPTION_NAME, 'encryption_failed', \__( 'Failed to encrypt API key. Please try again.', 'ai-image-renamer' ) );
                        // Keep old key if encryption failed.
                        $sanitized[ 'api_key' ] = $old[ 'api_key' ] ?? '';
                    }
                }
            }
        }

        // Enabled toggle.
        $sanitized[ 'enabled' ] = isset( $input[ 'enabled' ] ) && '1' === $input[ 'enabled' ];

        // File types.
        if ( isset( $input[ 'file_types' ] ) && is_array( $input[ 'file_types' ] ) ) {
            $allowed_types = [ 'image/jpeg', 'image/png', 'image/webp', 'image/gif' ];

            /**
             * Filter the allowed file types for validation.
             * Pro can add additional file types (e.g., AVIF, HEIC).
             *
             * @param  array  $allowed_types  The allowed MIME types.
             *
             * @since 1.0.0
             */
            $allowed_types = \apply_filters( 'air_allowed_file_types_for_validation', $allowed_types );

            $sanitized[ 'file_types' ] = array_intersect( $input[ 'file_types' ], $allowed_types );
        }

        // Max keywords.
        if ( isset( $input[ 'max_keywords' ] ) ) {
            $sanitized[ 'max_keywords' ] = \absint( $input[ 'max_keywords' ] );
            $sanitized[ 'max_keywords' ] = max( 1, min( 10, $sanitized[ 'max_keywords' ] ) );
        }

        // Alt text toggle.
        // Note: The UI says "Add to alt Attribute", key is set_alt_text
        $sanitized[ 'set_alt_text' ] = isset( $input[ 'set_alt_text' ] ) && '1' === $input[ 'set_alt_text' ];

        // Model selection.
        if ( isset( $input[ 'model' ] ) ) {
            $valid_models = [
                'meta-llama/llama-4-maverick-17b-128e-instruct',
                'meta-llama/llama-4-scout-17b-16e-instruct',
            ];

            /**
             * Filter the valid model IDs for validation.
             * Pro can add additional models here.
             *
             * @param  array  $valid_models  Array of valid model IDs.
             *
             * @since 1.0.0
             */
            $valid_models = \apply_filters( 'air_valid_models', $valid_models );

            if ( in_array( $input[ 'model' ], $valid_models, true ) ) {
                $sanitized[ 'model' ] = $input[ 'model' ];
            } else {
                // Invalid model submitted, add error and use default.
                \add_settings_error( self::OPTION_NAME, 'invalid_model', \__( 'Invalid AI model selected. Using default model.', 'ai-image-renamer' ) );
                $sanitized[ 'model' ] = $this->get_defaults()[ 'model' ];
            }
        }

        /**
         * Filter the sanitized settings before saving.
         * Pro can sanitize its own settings here.
         *
         * @param  array  $sanitized  The sanitized settings.
         * @param  array  $input      The raw input settings.
         * @param  array  $old        The previous settings.
         *
         * @since 1.0.0
         */
        return \apply_filters( 'air_sanitize_settings', $sanitized, $input, $old );
    }

    /**
     * Render the API key field.
     *
     * @return void
     */
    final public function render_api_key_field(): void {
        $options       = \get_option( self::OPTION_NAME, $this->get_defaults() );
        $encrypted_key = $options[ 'api_key' ] ?? '';
        $decrypted_key = '';

        if ( ! empty( $encrypted_key ) ) {
            $decrypted_key = $this->encryption_service->decrypt( $encrypted_key );
            if ( false === $decrypted_key ) {
                $decrypted_key = '';
            }
        }

        $saved = ! empty( $encrypted_key );

        // Mask the API key for display.
        $display_key = $saved && ! empty( $decrypted_key ) ? API_Key_Validator::mask_for_display( $decrypted_key ) : '';

        // Set placeholder based on whether a key is saved.
        $placeholder = $saved ? __( 'Type to overwrite existing key...', 'ai-image-renamer' ) : 'gsk_...';

        echo $this->template_engine->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'admin/fields/api-key.twig', [
            'option_name' => self::OPTION_NAME,
            'display_key' => $display_key,
            'placeholder' => $placeholder,
            'saved'       => $saved,
        ] );
    }

    /**
     * Get available file types.
     *
     * @return array
     */
    private function get_available_file_types(): array {
        $available_types = [
            'image/jpeg' => 'JPEG',
            'image/png'  => 'PNG',
            'image/webp' => 'WebP',
            'image/gif'  => 'GIF',
        ];

        /**
         * Filter the available file types displayed in the UI.
         * Pro can add additional file types (e.g., AVIF, HEIC).
         *
         * @param  array  $available_types  Array of MIME type => Label.
         *
         * @since 1.0.0
         */
        return \apply_filters( 'air_available_file_types', $available_types );
    }

    /**
     * Get available models.
     *
     * @param  string  $current  Current model ID.
     *
     * @return array
     */
    private function get_available_models( string $current ): array {
        $models = [
            'meta-llama/llama-4-maverick-17b-128e-instruct' => [
                'label'      => 'Llama 4 Maverick',
                'provider'   => 'meta',
                'desc'       => 'Best for detailed image analysis. Generates more accurate filenames and alt text for complex images.',
                'highlights' => [ 'params' ],
                'specs'      => [
                    'params'  => '17B (128 Experts)',
                    'context' => '128k',
                    'speed'   => '600 tokens/s',
                    'size'    => '20 MB',
                ],
            ],
            'meta-llama/llama-4-scout-17b-16e-instruct'     => [
                'label'      => 'Llama 4 Scout',
                'provider'   => 'meta',
                'desc'       => 'Best for speed. Renames images faster while maintaining good accuracy.',
                'highlights' => [ 'speed' ],
                'specs'      => [
                    'params'  => '17B (16 Experts)',
                    'context' => '128k',
                    'speed'   => '750 tokens/s',
                    'size'    => '20 MB',
                ],
            ],
        ];

        /**
         * Filter the available AI models displayed in the UI.
         * Pro can add additional models here.
         *
         * @param  array   $models   Array of model_id => ['label' => ..., 'desc' => ...].
         * @param  string  $current  The currently selected model ID.
         *
         * @since 1.0.0
         */
        return \apply_filters( 'air_available_models', $models, $current );
    }

    /**
     * Render the enabled toggle field.
     *
     * @return void
     */
    final public function render_enabled_field(): void {
        $options = \get_option( self::OPTION_NAME, $this->get_defaults() );
        $enabled = $options[ 'enabled' ] ?? true;

        echo $this->template_engine->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'admin/fields/enabled.twig', [
            'option_name' => self::OPTION_NAME,
            'enabled'     => $enabled,
        ] );
    }

    /**
     * Render the alt text toggle field.
     *
     * @return void
     */
    final public function render_alt_text_field(): void {
        $options = \get_option( self::OPTION_NAME, $this->get_defaults() );
        $set_alt = $options[ 'set_alt_text' ] ?? false;

        echo $this->template_engine->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'admin/fields/alt-text.twig', [
            'option_name' => self::OPTION_NAME,
            'set_alt'     => $set_alt,
        ] );
    }

    /**
     * Render the file types field.
     *
     * @return void
     */
    final public function render_file_types_field(): void {
        $options    = \get_option( self::OPTION_NAME, $this->get_defaults() );
        $file_types = $options[ 'file_types' ] ?? [];

        $available_types = $this->get_available_file_types();

        echo $this->template_engine->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'admin/fields/file-types.twig', [
            'option_name'     => self::OPTION_NAME,
            'file_types'      => $file_types,
            'available_types' => $available_types,
        ] );
    }

    /**
     * Render the max keywords field.
     *
     * @return void
     */
    final public function render_max_keywords_field(): void {
        $options      = \get_option( self::OPTION_NAME, $this->get_defaults() );
        $max_keywords = $options[ 'max_keywords' ] ?? 5;

        echo $this->template_engine->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'admin/fields/max-keywords.twig', [
            'option_name'  => self::OPTION_NAME,
            'max_keywords' => $max_keywords,
        ] );
    }

    /**
     * Render the model selection field.
     *
     * @return void
     */
    final public function render_model_field(): void {
        $options = \get_option( self::OPTION_NAME, $this->get_defaults() );
        $current = $options[ 'model' ] ?? 'meta-llama/llama-4-maverick-17b-128e-instruct';
        $models  = $this->get_available_models( $current );

        echo $this->template_engine->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'admin/fields/model.twig', [
            'option_name' => self::OPTION_NAME,
            'current'     => $current,
            'models'      => $models,
            'asset_url'   => \plugins_url( 'assets', \dirname( __DIR__, 2 ) . '/ai-image-renamer.php' ),
        ] );
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    final public function render_settings_page(): void {
        if ( ! \current_user_can( 'manage_options' ) ) {
            return;
        }

        $options       = \get_option( self::OPTION_NAME, $this->get_defaults() );
        $encrypted_key = $options[ 'api_key' ] ?? '';
        $decrypted_key = '';

        if ( ! empty( $encrypted_key ) ) {
            $decrypted_key = $this->encryption_service->decrypt( $encrypted_key );
            if ( false === $decrypted_key ) {
                $decrypted_key = '';
            }
        }

        $saved       = ! empty( $encrypted_key );
        $display_key = $saved && ! empty( $decrypted_key ) ? API_Key_Validator::mask_for_display( $decrypted_key ) : '';
        $placeholder = $saved ? __( 'Type to overwrite existing key...', 'ai-image-renamer' ) : 'gsk_...';

        // File types
        $file_types      = $options[ 'file_types' ] ?? [];
        $available_types = $this->get_available_file_types();

        // Models
        $current = $options[ 'model' ] ?? 'meta-llama/llama-4-maverick-17b-128e-instruct';
        $models  = $this->get_available_models( $current );

        echo $this->template_engine->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'admin/settings.twig', [
            'page_slug'       => self::PAGE_SLUG,
            'option_group'    => self::OPTION_GROUP,
            'option_name'     => self::OPTION_NAME,
            'version'         => AIR_VERSION,
            'page_title'      => get_admin_page_title(),
            'display_key'     => $display_key,
            'placeholder'     => $placeholder,
            'saved'           => $saved,
            'enabled'         => $options[ 'enabled' ] ?? true,
            'set_alt_text'    => $options[ 'set_alt_text' ] ?? false,
            'file_types'      => $file_types,
            'available_types' => $available_types,
            'current'         => $current,
            'models'          => $models,
            'max_keywords'    => $options[ 'max_keywords' ] ?? 5,
            'asset_url'       => \plugins_url( 'assets', \dirname( __DIR__, 2 ) . '/ai-image-renamer.php' ),
            'diagnostics'     => [
                'php'    => [
                    'label' => __( 'PHP Version', 'ai-image-renamer' ),
                    'value' => PHP_VERSION,
                    'ok'    => version_compare( PHP_VERSION, '8.2', '>=' ),
                    'desc'  => __( 'Required: 8.2 or higher', 'ai-image-renamer' ),
                ],
                'wp'     => [
                    'label' => __( 'WordPress', 'ai-image-renamer' ),
                    'value' => get_bloginfo( 'version' ),
                    'ok'    => version_compare( get_bloginfo( 'version' ), '6.0', '>=' ),
                    'desc'  => __( 'Required: 6.0 or higher', 'ai-image-renamer' ),
                ],
                'memory' => [
                    'label' => __( 'Memory Limit', 'ai-image-renamer' ),
                    'value' => ini_get( 'memory_limit' ),
                    'ok'    => true, // Informational
                    'desc'  => __( 'Allocated memory for script execution', 'ai-image-renamer' ),
                ],
                'upload' => [
                    'label' => __( 'Max Upload Size', 'ai-image-renamer' ),
                    'value' => ini_get( 'upload_max_filesize' ),
                    'ok'    => true, // Informational
                    'desc'  => __( 'Maximum file size set by server', 'ai-image-renamer' ),
                ],
                'curl'   => [
                    'label' => __( 'cURL Enabled', 'ai-image-renamer' ),
                    'value' => function_exists( 'curl_version' ) ? __( 'Yes', 'ai-image-renamer' ) : __( 'No', 'ai-image-renamer' ),
                    'ok'    => function_exists( 'curl_version' ),
                    'desc'  => __( 'Required for API communication', 'ai-image-renamer' ),
                ],
            ],
        ] );
    }


    /**
     * Enqueues admin-specific assets such as styles and scripts on the settings page.
     *
     * @param  string  $hook  The current admin page hook suffix.
     *
     * @return void
     */
    final public function enqueue_assets( string $hook ): void {
        if ( 'media_page_' . self::PAGE_SLUG !== $hook ) {
            return;
        }

        \wp_enqueue_style( 'air-admin', AIR_PLUGIN_URL . 'assets/css/admin.css', [], (string) filemtime( AIR_PLUGIN_DIR . 'assets/css/admin.css' ) );

        \wp_enqueue_script( 'air-admin', AIR_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery' ], AIR_VERSION, true );
        \wp_enqueue_script( 'air-admin-tabs', AIR_PLUGIN_URL . 'assets/js/admin-tabs.js', [ 'jquery' ], AIR_VERSION, true );

        \wp_localize_script( 'air-admin', 'airAdmin', [
            'ajaxUrl' => \admin_url( 'admin-ajax.php' ),
            'nonces'  => [
                'test_connection'           => \wp_create_nonce( 'air_test_connection' ),
                'delete_api_key'            => \wp_create_nonce( 'air_delete_api_key' ),
                'dismiss_encryption_notice' => \wp_create_nonce( 'air_dismiss_encryption_notice' ),
            ],
            'strings' => [
                'testing'           => \__( 'Testing...', 'ai-image-renamer' ),
                'success'           => \__( 'Connection successful!', 'ai-image-renamer' ),
                'error'             => \__( 'Connection failed:', 'ai-image-renamer' ),
                'no_key'            => \__( 'No API key configured.', 'ai-image-renamer' ),
                'delete_confirm'    => \__( 'Are you sure you want to delete the API Key? This action cannot be undone.', 'ai-image-renamer' ),
                'deleting'          => \__( 'Deleting...', 'ai-image-renamer' ),
                'enter_key'         => \__( 'Enter your Groq API key.', 'ai-image-renamer' ),
                'request_failed'    => \__( 'Request failed:', 'ai-image-renamer' ),
                'delete_key_button' => \__( 'Delete Key', 'ai-image-renamer' ),
            ],
        ] );

        \add_action( 'admin_footer', function () use ( $hook ) {
            if ( 'media_page_' . self::PAGE_SLUG !== $hook ) {
                return;
            }

            $sprite_path = AIR_PLUGIN_DIR . 'assets/icons/icons.svg';

            // Use SVG_Sanitizer to validate and sanitize the SVG content.
            $sanitized_svg = SVG_Sanitizer::load_and_sanitize_file( $sprite_path );

            if ( false !== $sanitized_svg ) {
                // Output is now safe after sanitization.
                echo $sanitized_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        } );
    }

    /**
     * Handle AJAX test connection request.
     *
     * @return void
     */
    final public function ajax_test_connection(): void {
        // Apply rate limiting: 10 requests per minute.
        if ( ! Rate_Limiter::check_rate_limit( 'air_test_connection', 10, 60 ) ) {
            Rate_Limiter::send_rate_limit_error( 'air_test_connection' );
        }

        \check_ajax_referer( 'air_test_connection', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( [ 'message' => \__( 'Permission denied.', 'ai-image-renamer' ) ] );
        }

        // Check if a specific key was provided in POST.
        // Use filter_input instead of direct $_POST access for better security.
        $api_key_raw = filter_input( INPUT_POST, 'api_key', FILTER_UNSAFE_RAW );
        $is_new_key  = filter_input( INPUT_POST, 'is_new_key', FILTER_VALIDATE_BOOLEAN );

        $api_key = null;

        if ( null !== $api_key_raw ) {
            $api_key = \sanitize_text_field( \wp_unslash( $api_key_raw ) );

            // If the key is masked and it's not explicitly marked as a new key, use saved key.
            if ( API_Key_Validator::is_masked( $api_key ) && ! $is_new_key ) {
                $api_key = null;
            } elseif ( empty( $api_key ) ) {
                // Explicitly empty key provided.
                \wp_send_json_error( [ 'message' => \__( 'No API key provided.', 'ai-image-renamer' ) ] );

                return;
            } elseif ( ! API_Key_Validator::is_masked( $api_key ) ) {
                // Only validate if it's not masked (i.e., it's a new key).
                // Validate the API key format before testing.
                $validation = API_Key_Validator::validate_groq_key( $api_key );
                if ( ! $validation[ 'valid' ] ) {
                    \wp_send_json_error( [ 'message' => $validation[ 'message' ] ] );

                    return;
                }
            }
        }

        $result = $this->groq_service->test_connection( $api_key );

        if ( true === $result ) {
            \wp_send_json_success( [ 'message' => \__( 'Connection successful!', 'ai-image-renamer' ) ] );
        } else {
            \wp_send_json_error( [ 'message' => $result ] );
        }
    }

    /**
     * Handle API key deletion.
     *
     * @return void
     */
    final public function ajax_delete_api_key(): void {
        // Apply rate limiting: 5 requests per minute.
        if ( ! Rate_Limiter::check_rate_limit( 'air_delete_api_key', 5, 60 ) ) {
            Rate_Limiter::send_rate_limit_error( 'air_delete_api_key' );
        }

        \check_ajax_referer( 'air_delete_api_key', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( [ 'message' => \__( 'Permission denied.', 'ai-image-renamer' ) ] );
        }

        $options              = \get_option( self::OPTION_NAME, $this->get_defaults() );
        $options[ 'api_key' ] = '';

        \update_option( self::OPTION_NAME, $options );

        \wp_send_json_success( [ 'message' => \__( 'API key deleted.', 'ai-image-renamer' ) ] );
    }

    /**
     * Show encryption security notice if key is not in wp-config.php.
     *
     * @return void
     */
    final public function show_encryption_notice(): void {
        // Only show on plugin settings page or general settings page.
        $screen = \get_current_screen();
        if ( ! $screen ) {
            return;
        }

        if ( 'tools_page_' . self::PAGE_SLUG !== $screen->id && 'options-general' !== $screen->id ) {
            return;
        }

        $this->encryption_service->maybe_show_security_notice();
    }

    /**
     * Handle AJAX request to dismiss encryption notice.
     *
     * @return void
     */
    final public function ajax_dismiss_encryption_notice(): void {
        \check_ajax_referer( 'air_dismiss_encryption_notice', 'nonce' );

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_send_json_error( [ 'message' => \__( 'Permission denied.', 'ai-image-renamer' ) ] );
        }

        \update_user_meta( \get_current_user_id(), 'air_encryption_notice_dismissed', true );

        \wp_send_json_success();
    }
}
