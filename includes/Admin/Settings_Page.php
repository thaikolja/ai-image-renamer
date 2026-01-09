<?php

/**
 * Admin Settings Page.
 *
 * @package AIR\Admin
 */

declare(strict_types=1);

namespace AIR\Admin;

use AIR\Services\Encryption_Service;
use AIR\Services\Groq_Service;
use AIR\Services\Template_Engine;

/**
 * Class Settings_Page
 *
 * Handles the plugin settings page under Settings menu.
 */
class Settings_Page
{

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
     * @param Template_Engine    $template_engine    Template engine instance.
     * @param Encryption_Service $encryption_service Encryption service instance.
     * @param Groq_Service       $groq_service       Groq service instance.
     */
    public function __construct(
        Template_Engine $template_engine,
        Encryption_Service $encryption_service,
        Groq_Service $groq_service
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
    public function init(): void
    {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_air_test_connection', array($this, 'ajax_test_connection'));
    }

    /**
     * Add the settings page to the admin menu.
     *
     * @return void
     */
    public function add_settings_page(): void
    {
        add_options_page(
            __('AI Image Renamer', 'ai-image-renamer'),
            __('AI Image Renamer', 'ai-image-renamer'),
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register plugin settings.
     *
     * @return void
     */
    public function register_settings(): void
    {
        register_setting(
            self::OPTION_GROUP,
            self::OPTION_NAME,
            array(
                'type'              => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default'           => $this->get_defaults(),
            )
        );

        // Main settings section.
        add_settings_section(
            'air_main_section',
            __('API Configuration', 'ai-image-renamer'),
            function () {
                echo '<p>' . esc_html__('Configure your Groq API settings below.', 'ai-image-renamer') . '</p>';
            },
            self::PAGE_SLUG
        );

        // API Key field.
        add_settings_field(
            'api_key',
            __('Groq API Key', 'ai-image-renamer'),
            array($this, 'render_api_key_field'),
            self::PAGE_SLUG,
            'air_main_section'
        );

        // Enable/Disable toggle.
        add_settings_field(
            'enabled',
            __('Enable Auto-Rename', 'ai-image-renamer'),
            array($this, 'render_enabled_field'),
            self::PAGE_SLUG,
            'air_main_section'
        );

        // File types section.
        add_settings_section(
            'air_file_types_section',
            __('File Types', 'ai-image-renamer'),
            function () {
                echo '<p>' . esc_html__('Select which image types to process.', 'ai-image-renamer') . '</p>';
            },
            self::PAGE_SLUG
        );

        add_settings_field(
            'file_types',
            __('Allowed Types', 'ai-image-renamer'),
            array($this, 'render_file_types_field'),
            self::PAGE_SLUG,
            'air_file_types_section'
        );

        // Advanced section.
        add_settings_section(
            'air_advanced_section',
            __('Advanced Settings', 'ai-image-renamer'),
            function () {
                echo '<p>' . esc_html__('Customize the AI prompt and keyword settings.', 'ai-image-renamer') . '</p>';
            },
            self::PAGE_SLUG
        );

        add_settings_field(
            'custom_prompt',
            __('Custom Prompt', 'ai-image-renamer'),
            array($this, 'render_custom_prompt_field'),
            self::PAGE_SLUG,
            'air_advanced_section'
        );

        add_settings_field(
            'max_keywords',
            __('Max Keywords', 'ai-image-renamer'),
            array($this, 'render_max_keywords_field'),
            self::PAGE_SLUG,
            'air_advanced_section'
        );
    }

    /**
     * Get default settings.
     *
     * @return array Default settings.
     */
    private function get_defaults(): array
    {
        return array(
            'api_key'       => '',
            'enabled'       => true,
            'file_types'    => array('image/jpeg', 'image/png', 'image/webp', 'image/gif'),
            'custom_prompt' => '',
            'max_keywords'  => 5,
        );
    }

    /**
     * Sanitize settings before saving.
     *
     * @param array $input The input settings.
     *
     * @return array Sanitized settings.
     */
    public function sanitize_settings(array $input): array
    {
        $sanitized = $this->get_defaults();
        $old       = get_option(self::OPTION_NAME, $this->get_defaults());

        // Handle API key encryption.
        // Always preserve the old API key first.
        $sanitized['api_key'] = $old['api_key'] ?? '';

        // Only update the API key if a new one was provided.
        if (isset($input['api_key']) && ! empty(trim($input['api_key']))) {
            $plaintext = trim($input['api_key']);

            // If the key starts with 'gsk_', it's a new plaintext key.
            if (str_starts_with($plaintext, 'gsk_')) {
                $encrypted = $this->encryption_service->encrypt($plaintext);
                if (false !== $encrypted) {
                    $sanitized['api_key'] = $encrypted;
                } else {
                    add_settings_error(
                        self::OPTION_NAME,
                        'encryption_failed',
                        __('Failed to encrypt API key. Please try again.', 'ai-image-renamer'),
                        'error'
                    );
                }
            }
            // If not starting with gsk_, keep the old key (already set above).
        }

        // Enabled toggle.
        $sanitized['enabled'] = isset($input['enabled']) && '1' === $input['enabled'];

        // File types.
        if (isset($input['file_types']) && is_array($input['file_types'])) {
            $allowed_types            = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
            $sanitized['file_types'] = array_intersect($input['file_types'], $allowed_types);
        }

        // Custom prompt.
        if (isset($input['custom_prompt'])) {
            $sanitized['custom_prompt'] = sanitize_textarea_field($input['custom_prompt']);
        }

        // Max keywords.
        if (isset($input['max_keywords'])) {
            $sanitized['max_keywords'] = absint($input['max_keywords']);
            $sanitized['max_keywords'] = max(1, min(10, $sanitized['max_keywords']));
        }

        return $sanitized;
    }

    /**
     * Render the API key field.
     *
     * @return void
     */
    public function render_api_key_field(): void
    {
        $options = get_option(self::OPTION_NAME, $this->get_defaults());
        $api_key = $options['api_key'] ?? '';

        // Show masked encrypted key or empty input.
        $display_value = '';
        if (! empty($api_key)) {
            // Show first 10 characters of encrypted key to indicate it's set.
            $display_value = substr($api_key, 0, 20) . '...';
        }

?>
        <input type="text"
            id="air_api_key"
            name="<?php echo esc_attr(self::OPTION_NAME); ?>[api_key]"
            value=""
            class="regular-text"
            placeholder="<?php echo esc_attr($display_value ? $display_value : 'gsk_...'); ?>"
            autocomplete="off" />
        <p class="description">
            <?php if (! empty($api_key)) : ?>
                <?php esc_html_e('API key is set (encrypted). Enter a new key to replace it.', 'ai-image-renamer'); ?>
            <?php else : ?>
                <?php esc_html_e('Enter your Groq API key. It will be encrypted before storage.', 'ai-image-renamer'); ?>
            <?php endif; ?>
        </p>
        <p>
            <button type="button" id="air_test_connection" class="button button-secondary">
                <?php esc_html_e('Test Connection', 'ai-image-renamer'); ?>
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
    public function render_enabled_field(): void
    {
        $options = get_option(self::OPTION_NAME, $this->get_defaults());
        $enabled = $options['enabled'] ?? true;
    ?>
        <label>
            <input type="checkbox"
                name="<?php echo esc_attr(self::OPTION_NAME); ?>[enabled]"
                value="1"
                <?php checked($enabled); ?> />
            <?php esc_html_e('Automatically rename images on upload', 'ai-image-renamer'); ?>
        </label>
        <?php
    }

    /**
     * Render the file types field.
     *
     * @return void
     */
    public function render_file_types_field(): void
    {
        $options    = get_option(self::OPTION_NAME, $this->get_defaults());
        $file_types = $options['file_types'] ?? array();

        $available_types = array(
            'image/jpeg' => 'JPEG',
            'image/png'  => 'PNG',
            'image/webp' => 'WebP',
            'image/gif'  => 'GIF',
        );

        foreach ($available_types as $mime => $label) {
            $checked = in_array($mime, $file_types, true);
        ?>
            <label style="margin-right: 15px;">
                <input type="checkbox"
                    name="<?php echo esc_attr(self::OPTION_NAME); ?>[file_types][]"
                    value="<?php echo esc_attr($mime); ?>"
                    <?php checked($checked); ?> />
                <?php echo esc_html($label); ?>
            </label>
        <?php
        }
    }

    /**
     * Render the custom prompt field.
     *
     * @return void
     */
    public function render_custom_prompt_field(): void
    {
        $options       = get_option(self::OPTION_NAME, $this->get_defaults());
        $custom_prompt = $options['custom_prompt'] ?? '';
        $default       = 'View this image and describe it in no more than 5 keywords. Only return the output.';
        ?>
        <textarea id="air_custom_prompt"
            name="<?php echo esc_attr(self::OPTION_NAME); ?>[custom_prompt]"
            rows="3"
            class="large-text"
            placeholder="<?php echo esc_attr($default); ?>"><?php echo esc_textarea($custom_prompt); ?></textarea>
        <p class="description">
            <?php esc_html_e('Leave empty to use the default prompt.', 'ai-image-renamer'); ?>
        </p>
    <?php
    }

    /**
     * Render the max keywords field.
     *
     * @return void
     */
    public function render_max_keywords_field(): void
    {
        $options      = get_option(self::OPTION_NAME, $this->get_defaults());
        $max_keywords = $options['max_keywords'] ?? 5;
    ?>
        <select name="<?php echo esc_attr(self::OPTION_NAME); ?>[max_keywords]" id="air_max_keywords">
            <?php for ($i = 1; $i <= 10; $i++) : ?>
                <option value="<?php echo esc_attr($i); ?>" <?php selected($max_keywords, $i); ?>>
                    <?php echo esc_html($i); ?>
                </option>
            <?php endfor; ?>
        </select>
        <p class="description">
            <?php esc_html_e('Maximum number of keywords to generate for filenames.', 'ai-image-renamer'); ?>
        </p>
<?php
    }

    /**
     * Render the settings page.
     *
     * @return void
     */
    public function render_settings_page(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        echo $this->template_engine->render('admin/settings.twig', array( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'page_slug'    => self::PAGE_SLUG,
            'option_group' => self::OPTION_GROUP,
        ));
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook The current admin page hook.
     *
     * @return void
     */
    public function enqueue_assets(string $hook): void
    {
        if ('settings_page_' . self::PAGE_SLUG !== $hook) {
            return;
        }

        wp_enqueue_style(
            'air-admin',
            AIR_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            AIR_VERSION
        );

        wp_enqueue_script(
            'air-admin',
            AIR_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            AIR_VERSION,
            true
        );

        wp_localize_script(
            'air-admin',
            'airAdmin',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('air_test_connection'),
                'strings' => array(
                    'testing'  => __('Testing...', 'ai-image-renamer'),
                    'success'  => __('Connection successful!', 'ai-image-renamer'),
                    'error'    => __('Connection failed:', 'ai-image-renamer'),
                    'no_key'   => __('No API key configured.', 'ai-image-renamer'),
                ),
            )
        );
    }

    /**
     * Handle AJAX test connection request.
     *
     * @return void
     */
    public function ajax_test_connection(): void
    {
        check_ajax_referer('air_test_connection', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'ai-image-renamer')));
        }

        $result = $this->groq_service->test_connection();

        if (true === $result) {
            wp_send_json_success(array('message' => __('Connection successful!', 'ai-image-renamer')));
        } else {
            wp_send_json_error(array('message' => $result));
        }
    }
}
