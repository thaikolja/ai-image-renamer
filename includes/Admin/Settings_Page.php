<?php

/**
 * Admin Settings Page.
 *
 * @package AIR\Admin
 */

declare( strict_types=1 );

namespace AIR\Admin;

use AIR\Services\Encryption_Service;
use AIR\Services\Groq_Service;
use AIR\Services\Template_Engine;

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
		\add_action( 'wp_ajax_air_test_connection', [ $this, 'ajax_test_connection' ] );
		\add_action( 'wp_ajax_air_delete_api_key', [ $this, 'ajax_delete_api_key' ] );
	}

	/**
	 * Add the settings page to the admin menu.
	 *
	 * @return void
	 */
	final public function add_settings_page(): void {
		\add_options_page( \__( 'AI Image Renamer', 'ai-image-renamer' ), \__( 'AI Image Renamer', 'ai-image-renamer' ), 'manage_options', self::PAGE_SLUG, [ $this, 'render_settings_page' ] );
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
			?>
            <div class="air-settings-hero">
                <div class="air-hero-column air-hero-column-narrow postbox">
                    <div class="postbox-header">
                        <div class="handle-actions">
                            <button
                                    type="button"
                                    class="handlediv"
                                    aria-expanded="true">
                                <span
                                        class="toggle-indicator"
                                        aria-hidden="true"></span>
                            </button>
                        </div>
                        <h2 class="hndle ui-sortable-handle"><span><span class="dashicons dashicons-editor-help"></span> <?php \esc_html_e( 'How does it work?', 'ai-image-renamer' ); ?></span></h2>
                    </div>
                    <div class="inside">
                        <p><?php \esc_html_e( 'The plugin uses the powerful Groq Vision API to analyze your uploaded images. It extracts descriptive keywords and automatically renames your files for better SEO and accessibility.', 'ai-image-renamer' ); ?></p>
                    </div>
                </div>
                <div class="air-hero-column air-hero-column-wide postbox pro">
                    <div class="postbox-header">
                        <div class="handle-actions">
                            <button
                                    type="button"
                                    class="handlediv"
                                    aria-expanded="true">
                                <span
                                        class="toggle-indicator"
                                        aria-hidden="true"></span>
                            </button>
                        </div>
                        <h2 class="hndle ui-sortable-handle"><span><span class="dashicons dashicons-smiley"></span> <?php \esc_html_e( 'Is it free?', 'ai-image-renamer' ); ?></span></h2>
                    </div>
                    <div class="inside">
                        <p><?php \esc_html_e( 'Yes! This plugin leverages Groq\'s generous free tier. Simply get your free API key from the Groq console and start renaming your images without any costs.', 'ai-image-renamer' ); ?></p>
                        <p><?php \esc_html_e( 'Unlock advanced features like bulk renaming, custom naming patterns, and support for 20+ languages. Coming soon to take your media management to the next level.', 'ai-image-renamer' ); ?></p>
                    </div>
                </div>
            </div>
			<?php
		}, self::PAGE_SLUG );

		// Enable/Disable toggle.
		\add_settings_field( 'enabled', '<label for="air_enabled"><span class="dashicons dashicons-lightbulb"></span> ' . \__( 'Enable Auto-Rename', 'ai-image-renamer' ) . '</label>', [
			$this,
			'render_enabled_field'
		], self::PAGE_SLUG, 'air_main_section' );

		// API Key field.
		\add_settings_field( 'api_key', '<label for="air_api_key"><span class="dashicons dashicons-admin-network"></span> ' . \__( 'Groq API Key', 'ai-image-renamer' ) . '</label>', [
			$this,
			'render_api_key_field'
		], self::PAGE_SLUG, 'air_main_section' );

		// Model Selection.
		\add_settings_field( 'model', '<label for="air_model"><span class="dashicons dashicons-products"></span> ' . \__( 'AI Model', 'ai-image-renamer' ) . '</label>', [
			$this,
			'render_model_field'
		], self::PAGE_SLUG, 'air_main_section' );

		// Alt text toggle.
		\add_settings_field( 'set_alt_text', '<label for="air_set_alt_text"><span class="dashicons dashicons-text"></span> ' . \__( 'Use as <code>alt=""</code>', 'ai-image-renamer' ) . '</label>', [
			$this,
			'render_alt_text_field'
		], self::PAGE_SLUG, 'air_main_section' );

		// File types section.
		\add_settings_section( 'air_file_types_section', '<span class="dashicons dashicons-format-image"></span> ' . \__( 'File Types', 'ai-image-renamer' ), function () {
			echo '<p>' . \esc_html__( 'Select which image types to process.', 'ai-image-renamer' ) . '</p>';
		}, self::PAGE_SLUG );

		\add_settings_field( 'file_types', '<span class="dashicons dashicons-images-alt2"></span> ' . \__( 'Allowed Types', 'ai-image-renamer' ), [
			$this,
			'render_file_types_field'
		], self::PAGE_SLUG, 'air_file_types_section' );

		// Advanced section.
		\add_settings_section( 'air_advanced_section', '<span class="dashicons dashicons-admin-tools"></span> ' . \__( 'Advanced Settings', 'ai-image-renamer' ), function () {
			echo '<p>' . \esc_html__( 'Customize the AI prompt and keyword settings.', 'ai-image-renamer' ) . '</p>';
		}, self::PAGE_SLUG );

		\add_settings_field( 'custom_prompt', '<label for="air_custom_prompt"><span class="dashicons dashicons-editor-quote"></span> ' . \__( 'Custom Prompt', 'ai-image-renamer' ) . '</label>', [
			$this,
			'render_custom_prompt_field'
		], self::PAGE_SLUG, 'air_advanced_section' );

		\add_settings_field( 'max_keywords', '<label for="air_max_keywords"><span class="dashicons dashicons-editor-ol"></span> ' . \__( 'Max Keywords', 'ai-image-renamer' ) . '</label>', [
			$this,
			'render_max_keywords_field'
		], self::PAGE_SLUG, 'air_advanced_section' );
	}

	/**
	 * Get default settings.
	 *
	 * @return array Default settings.
	 */
	private function get_defaults(): array {
		return [
			'api_key'       => '',
			'enabled'       => true,
			'file_types'    => [ 'image/jpeg', 'image/png', 'image/webp', 'image/gif' ],
			'custom_prompt' => '',
			'max_keywords'  => 5,
			'set_alt_text'  => false,
			'model'         => 'meta-llama/llama-4-maverick-17b-128e-instruct',
		];
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
			} elseif ( str_starts_with( $plaintext, 'gsk_' ) ) {
				$encrypted = $this->encryption_service->encrypt( $plaintext );
				if ( false !== $encrypted ) {
					$sanitized[ 'api_key' ] = $encrypted;
				} else {
					\add_settings_error( self::OPTION_NAME, 'encryption_failed', \__( 'Failed to encrypt API key. Please try again.', 'ai-image-renamer' ) );
					// Keep old key if encryption failed.
					$sanitized[ 'api_key' ] = $old[ 'api_key' ] ?? '';
				}
			} else {
				// Invalid format, maybe keep old or show error.
				// For now, let's assume if it doesn't start with gsk_, it's invalid.
				\add_settings_error( self::OPTION_NAME, 'invalid_key', \__( 'Invalid API Key format. Must start with <code>gsk_</code>.', 'ai-image-renamer' ) );
				$sanitized[ 'api_key' ] = $old[ 'api_key' ] ?? '';
			}
		}

		// Enabled toggle.
		$sanitized[ 'enabled' ] = isset( $input[ 'enabled' ] ) && '1' === $input[ 'enabled' ];

		// File types.
		if ( isset( $input[ 'file_types' ] ) && is_array( $input[ 'file_types' ] ) ) {
			$allowed_types             = [ 'image/jpeg', 'image/png', 'image/webp', 'image/gif' ];
			$sanitized[ 'file_types' ] = array_intersect( $input[ 'file_types' ], $allowed_types );
		}

		// Custom prompt.
		if ( isset( $input[ 'custom_prompt' ] ) ) {
			$sanitized[ 'custom_prompt' ] = \sanitize_textarea_field( $input[ 'custom_prompt' ] );
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
			if ( in_array( $input[ 'model' ], $valid_models, true ) ) {
				$sanitized[ 'model' ] = $input[ 'model' ];
			}
		}

		return $sanitized;
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
		?>
        <div class="air-api-key-container">
            <input
                    type="text"
                    id="air_api_key"
                    name="<?php echo \esc_attr( self::OPTION_NAME ); ?>[api_key]"
                    value="<?php echo \esc_attr( $decrypted_key ); ?>"
                    class="regular-text"
                    placeholder="gsk_..."
                    autocomplete="off" />

            <button
                    type="button"
                    id="air_delete_api_key"
                    class="button button-link-delete">
				<?php \esc_html_e( 'Delete Key', 'ai-image-renamer' ); ?>
            </button>
        </div>
        <p class="description">
			<?php if ( $saved ) : ?><?php \esc_html_e( 'Your Groq API key has been encrypted and saved', 'ai-image-renamer' ); ?><?php else : ?><?php \esc_html_e( 'Enter your Groq API key.', 'ai-image-renamer' ); ?><?php endif; ?>
        </p>
        <p class="air-action-buttons">
            <a
                    href="https://console.groq.com/keys"
                    target="_blank"
                    class="button button-primary air-button-with-icon"> <span class="dashicons dashicons-admin-network"></span>
				<?php \esc_html_e( 'Get Free Groq API Key', 'ai-image-renamer' ); ?>
            </a>
            <button
                    type="button"
                    id="air_test_connection"
                    class="button button-secondary air-button-with-icon">
                <span class="dashicons dashicons-update"></span>
				<?php \esc_html_e( 'Test Connection', 'ai-image-renamer' ); ?>
            </button>
            <span id="air_test_result"></span>
        </p>
		<?php
	}

	/**
	 * Render the enabled toggle field.
	 *
	 * @return void
	 */
	final public function render_enabled_field(): void {
		$options = \get_option( self::OPTION_NAME, $this->get_defaults() );
		$enabled = $options[ 'enabled' ] ?? true;
		?>
        <label> <input
                    type="checkbox"
                    id="air_enabled"
                    name="<?php echo \esc_attr( self::OPTION_NAME ); ?>[enabled]"
                    value="1"
				<?php \checked( $enabled ); ?> />
			<?php \esc_html_e( 'Automatically rename images on upload', 'ai-image-renamer' ); ?>
        </label>
		<?php
	}

	/**
	 * Render the alt text toggle field.
	 *
	 * @return void
	 */
	final public function render_alt_text_field(): void {
		$options = \get_option( self::OPTION_NAME, $this->get_defaults() );
		$set_alt = $options[ 'set_alt_text' ] ?? false;
		?>
        <label> <input
                    type="checkbox"
                    id="air_set_alt_text"
                    name="<?php echo \esc_attr( self::OPTION_NAME ); ?>[set_alt_text]"
                    value="1"
				<?php \checked( $set_alt ); ?> />
			<?php \esc_html_e( 'Automatically set images\' "alt" attribute from the filename.', 'ai-image-renamer' ); ?>
        </label>
		<?php
	}

	/**
	 * Render the file types field.
	 *
	 * @return void
	 */
	final public function render_file_types_field(): void {
		$options    = \get_option( self::OPTION_NAME, $this->get_defaults() );
		$file_types = $options[ 'file_types' ] ?? [];

		$available_types = [
			'image/jpeg' => 'JPEG',
			'image/png'  => 'PNG',
			'image/webp' => 'WebP',
			'image/gif'  => 'GIF',
		];

		foreach ( $available_types as $mime => $label ) {
			$checked = in_array( $mime, $file_types, true );
			?>
            <label class="air-file-type-label"> <input
                        type="checkbox"
                        name="<?php echo \esc_attr( self::OPTION_NAME ); ?>[file_types][]"
                        value="<?php echo \esc_attr( $mime ); ?>"
					<?php \checked( $checked ); ?> />
				<?php echo \esc_html( $label ); ?>
            </label>
			<?php
		}
	}

	/**
	 * Render the custom prompt field.
	 *
	 * @return void
	 */
	final public function render_custom_prompt_field(): void {
		$options       = \get_option( self::OPTION_NAME, $this->get_defaults() );
		$custom_prompt = $options[ 'custom_prompt' ] ?? '';
		$default       = 'View this image and describe it in no more than 5 keywords. Only return the output.';
		?>
        <textarea
                id="air_custom_prompt"
                name="<?php echo \esc_attr( self::OPTION_NAME ); ?>[custom_prompt]"
                rows="3"
                class="large-text"
                placeholder="<?php echo \esc_attr( $default ); ?>"><?php echo \esc_textarea( $custom_prompt ); ?></textarea>
        <p class="description">
			<?php \esc_html_e( 'Leave empty to use the default prompt.', 'ai-image-renamer' ); ?>
        </p>
		<?php
	}

	/**
	 * Render the max keywords field.
	 *
	 * @return void
	 */
	final public function render_max_keywords_field(): void {
		$options      = \get_option( self::OPTION_NAME, $this->get_defaults() );
		$max_keywords = $options[ 'max_keywords' ] ?? 5;
		?>
        <select
                name="<?php echo \esc_attr( self::OPTION_NAME ); ?>[max_keywords]"
                id="air_max_keywords">
			<?php for ( $i = 1; $i <= 10; $i ++ ) : ?>
                <option value="<?php echo \esc_attr( $i ); ?>" <?php \selected( $max_keywords, $i ); ?>>
					<?php echo \esc_html( $i ); ?>
                </option>
			<?php endfor; ?>
        </select>
        <p class="description">
			<?php \esc_html_e( 'Maximum number of keywords to generate for filenames.', 'ai-image-renamer' ); ?>
        </p>
		<?php
	}

	/**
	 * Render the model selection field.
	 *
	 * @return void
	 */
	final public function render_model_field(): void {
		$options = \get_option( self::OPTION_NAME, $this->get_defaults() );
		$current = $options[ 'model' ] ?? 'meta-llama/llama-4-maverick-17b-128e-instruct';
		$models  = [
			'meta-llama/llama-4-maverick-17b-128e-instruct' => [
				'label' => 'Maverick',
				'desc'  => 'High-performance model for detailed and creative image analysis.',
			],
			'meta-llama/llama-4-scout-17b-16e-instruct'     => [
				'label' => 'Scout',
				'desc'  => 'Lightweight model optimized for speed and efficiency.',
			],
		];
		?>
        <div class="air-model-selector">
			<?php
			$is_first = true;
			foreach ( $models as $id => $info ) :
				$is_checked = ( $current === $id );
				?>
                <label class="air-model-card <?php echo $is_checked ? 'selected' : ''; ?>"> <input
                            type="radio"
							<?php if ( $is_first ) : ?>id="air_model"<?php endif; ?>
                            name="<?php echo \esc_attr( self::OPTION_NAME ); ?>[model]"
                            value="<?php echo \esc_attr( $id ); ?>"
						<?php \checked( $current, $id ); ?> />
                    <div class="air-model-content">
                        <strong><?php echo \esc_html( $info[ 'label' ] ); ?></strong>
                        <p><?php echo \esc_html( $info[ 'desc' ] ); ?></p>
                    </div>
                </label>
				<?php
				$is_first = false;
			endforeach;
			?>
        </div>
		<?php
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

		echo $this->template_engine->render( 'admin/settings.twig', [ // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'page_slug'    => self::PAGE_SLUG,
			'option_group' => self::OPTION_GROUP,
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
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		\wp_enqueue_style( 'air-admin', AIR_PLUGIN_URL . 'assets/css/admin.css', [], AIR_VERSION );

		\wp_enqueue_script( 'air-admin', AIR_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery' ], AIR_VERSION, true );

		\wp_localize_script( 'air-admin', 'airAdmin', [
			'ajaxUrl' => \admin_url( 'admin-ajax.php' ),
			'nonce'   => \wp_create_nonce( 'air_test_connection' ),
			'strings' => [
				'testing' => \__( 'Testing...', 'ai-image-renamer' ),
				'success' => \__( 'Connection successful!', 'ai-image-renamer' ),
				'error'   => \__( 'Connection failed:', 'ai-image-renamer' ),
				'no_key'  => \__( 'No API key configured.', 'ai-image-renamer' ),
			],
		] );

		// Inject SVG sprite for icon support.
		\add_action( 'admin_footer', function () use ( $hook ) {
			if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
				return;
			}

			$sprite_path = AIR_PLUGIN_DIR . 'assets/icons/icons.svg';
			if ( \file_exists( $sprite_path ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG sprite is safe.
				echo \file_get_contents( $sprite_path );
			}
		} );
	}

	/**
	 * Handle AJAX test connection request.
	 *
	 * @return void
	 */
	final public function ajax_test_connection(): void {
		\check_ajax_referer( 'air_test_connection', 'nonce' );

		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( [ 'message' => \__( 'Permission denied.', 'ai-image-renamer' ) ] );
		}

		// Check if a specific key was provided in POST.
		// Use filter_input instead of direct $_POST access for better security.
		$api_key_raw = filter_input( INPUT_POST, 'api_key', FILTER_UNSAFE_RAW );

		$api_key = null;

		if ( null !== $api_key_raw ) {
			$api_key = \sanitize_text_field( \wp_unslash( $api_key_raw ) );

			// If the key is masked (contains bullets), treat it as null (use saved).
			if ( str_contains( $api_key, '•' ) ) {
				$api_key = null;
			} elseif ( empty( $api_key ) ) {
				// Explicitly empty key provided.
				\wp_send_json_error( [ 'message' => \__( 'No API key provided.', 'ai-image-renamer' ) ] );
				return;
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
		\check_ajax_referer( 'air_test_connection', 'nonce' ); // Reusing nonce for convenience

		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_send_json_error( [ 'message' => \__( 'Permission denied.', 'ai-image-renamer' ) ] );
		}

		$options              = \get_option( self::OPTION_NAME, $this->get_defaults() );
		$options[ 'api_key' ] = '';

		\update_option( self::OPTION_NAME, $options );

		\wp_send_json_success( [ 'message' => \__( 'API key deleted.', 'ai-image-renamer' ) ] );
	}
}
