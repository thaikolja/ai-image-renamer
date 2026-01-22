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
 * Twig Template Engine Service.
 *
 * @package AIR\Services
 */

declare( strict_types=1 );

namespace AIR\Services;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TwigFunction;

/**
 * Class Template_Engine
 *
 * Wrapper for Twig template rendering.
 */
class Template_Engine {
    /**
     * Twig environment instance.
     *
     * @var Environment
     */
    private Environment $twig;

    /**
     * Constructor.
     *
     * Initializes the Twig environment with the views directory.
     */
    public function __construct() {
        $views_path = AIR_PLUGIN_DIR . 'views';
        $cache_path = AIR_PLUGIN_DIR . 'cache/twig';

        // Validate views directory path.
        $real_views_path = \realpath( $views_path );
        $real_plugin_dir = \realpath( AIR_PLUGIN_DIR );

        if ( false === $real_views_path || false === $real_plugin_dir ) {
            \error_log( 'AI Image Renamer: Failed to resolve paths for Twig template engine.' );
            // Fall back to no caching if path resolution fails.
            $cache_path = false;
        } elseif ( ! str_starts_with( $real_views_path, $real_plugin_dir ) ) {
            \error_log( 'AI Image Renamer: Views directory is outside plugin directory.' );
            // Fall back to no caching for security.
            $cache_path = false;
        }

        // Create cache directory if it doesn't exist and caching is enabled.
        if ( $cache_path && ! \is_dir( $cache_path ) ) {
            $created = \wp_mkdir_p( $cache_path );
            if ( ! $created ) {
                \error_log( 'AI Image Renamer: Failed to create Twig cache directory.' );
                
                // Fall back to no caching if directory creation fails.
                $cache_path = false;
            }
        }

        // Validate cache directory is writable.
        if ( $cache_path && ! \is_writable( $cache_path ) ) {
            \error_log( 'AI Image Renamer: Twig cache directory is not writable.' );

            // Fall back to no caching if directory is not writable.
            $cache_path = false;
        }

        /**
         * Filter the template paths for Twig.
         * Pro can prepend its own views directory to override templates.
         * First path in the array takes precedence.
         *
         * @param  array  $paths  Array of absolute paths to template directories.
         *
         * @since 1.0.0
         *
         */
        $template_paths = \apply_filters( 'air_template_paths', [ $views_path ] );

        // Validate and filter paths - only include existing directories.
        $valid_paths = [];
        foreach ( $template_paths as $path ) {
            if ( \is_string( $path ) && \is_dir( $path ) ) {
                $valid_paths[] = $path;
            }
        }

        // Ensure we always have at least the core views path.
        if ( empty( $valid_paths ) ) {
            $valid_paths = [ $views_path ];
        }

        $loader     = new FilesystemLoader( $valid_paths );
        $this->twig = new Environment( $loader, [
            'cache'       => ( WP_DEBUG || false === $cache_path ) ? false : $cache_path,
            'auto_reload' => true,
            'debug'       => WP_DEBUG,
            'autoescape'  => 'html', // Enable auto-escaping for security
        ] );

        // Add WordPress-specific globals and functions.
        $this->register_globals();
        $this->register_functions();
    }

    /**
     * Register global variables available in all templates.
     *
     * @return void
     */
    private function register_globals(): void {
        $this->twig->addGlobal( 'plugin_url', AIR_PLUGIN_URL );
        $this->twig->addGlobal( 'plugin_version', AIR_VERSION );
    }

    /**
     * Register custom Twig functions for WordPress integration.
     *
     * @return void
     */
    private function register_functions(): void {
        // WordPress translation functions.
        $this->twig->addFunction( new TwigFunction( '__', function ( string $text, string $domain = 'ai-image-renamer' ): string {
            return \__( $text, $domain ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
        } ) );

        $this->twig->addFunction( new TwigFunction( 'esc_html', function ( string $text ): string {
            return \esc_html( $text );
        } ) );

        $this->twig->addFunction( new TwigFunction( 'esc_attr', function ( string $text ): string {
            return \esc_attr( $text );
        } ) );

        $this->twig->addFunction( new TwigFunction( 'wp_nonce_field', function ( string $action, string $name = '_wpnonce', bool $referer = true, bool $echo = false ): string {
            return \wp_nonce_field( $action, $name, $referer, $echo );
        } ) );

        $this->twig->addFunction( new TwigFunction( 'settings_fields', function ( string $option_group ): void {
            \settings_fields( $option_group );
        } ) );

        $this->twig->addFunction( new TwigFunction( 'do_settings_sections', function ( string $page ): void {
            \do_settings_sections( $page );
        } ) );

        $this->twig->addFunction( new TwigFunction( 'submit_button', function ( string $text = '', string $type = 'primary', string $name = 'submit', bool $wrap = true, $other_attributes = null ): void {
            \submit_button( $text, $type, $name, $wrap, $other_attributes );
        } ) );
    }

    /**
     * Render a Twig template.
     *
     * @param  string  $template  The template filename (relative to views/).
     * @param  array   $context   Variables to pass to the template.
     *
     * @return string The rendered HTML.
     */
    final public function render( string $template, array $context = [] ): string {
        try {
            return $this->twig->render( $template, $context );
        } catch ( LoaderError|RuntimeError|SyntaxError $e ) {
            if ( WP_DEBUG ) {
                return '<div class="notice notice-error"><p>' . \esc_html__( 'Template Error:', 'ai-image-renamer' ) . ' ' . \esc_html( $e->getMessage() ) . '</p></div>';
            }
            \error_log( 'AI Image Renamer Template Error: ' . $e->getMessage() );

            return '';
        }
    }
}
