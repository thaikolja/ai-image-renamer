# AI Image Renamer - Developer Guide

This guide explains how to extend the AI Image Renamer plugin using filters and actions.

## Available Hooks

### Plugin Lifecycle

| Hook | Type | Description |
|------|------|-------------|
| `air_services_loaded` | Action | Fires after core services init, before components |
| `air_loaded` | Action | Fires when plugin is fully initialized |

### Settings Page

| Hook | Type | Description |
|------|------|-------------|
| `air_settings_defaults` | Filter | Extend default settings |
| `air_register_settings_fields` | Action | Add custom settings sections/fields |
| `air_sanitize_settings` | Filter | Sanitize custom settings |
| `air_available_models` | Filter | Add AI models to UI dropdown |
| `air_valid_models` | Filter | Add model IDs for validation |
| `air_available_file_types` | Filter | Add file types to UI checkboxes |
| `air_allowed_file_types_for_validation` | Filter | Add MIME types for validation |

### AI / Groq Service

| Hook | Type | Description |
|------|------|-------------|
| `air_prompt` | Filter | Customize the AI prompt |
| `air_max_file_size` | Filter | Change max file size limit |
| `air_api_payload` | Filter | Modify API request payload |
| `air_api_request_args` | Filter | Modify HTTP request arguments |
| `air_api_response` | Action | Process/log API responses |
| `air_allowed_file_types` | Filter | Extend allowed MIME types |

### Image Upload Processing

| Hook | Type | Description |
|------|------|-------------|
| `air_should_process_upload` | Filter | Skip specific uploads |
| `air_generated_description` | Filter | Modify AI description |
| `air_new_filename` | Filter | Customize final filename |
| `air_image_renamed` | Action | After image is renamed |
| `air_alt_text` | Filter | Customize alt text |

### Template System

| Hook | Type | Description |
|------|------|-------------|
| `air_template_paths` | Filter | Add custom template directories |

---

## Examples

### Adding a New AI Model

```php
// Add to air_available_models for UI display
add_filter( 'air_available_models', function( $models ) {
    $models['openai/gpt-4-vision'] = [
        'label' => 'GPT-4 Vision',
        'desc'  => 'OpenAI GPT-4 with vision capabilities.',
    ];
    return $models;
});

// Add to air_valid_models for validation
add_filter( 'air_valid_models', function( $valid ) {
    $valid[] = 'openai/gpt-4-vision';
    return $valid;
});
```

### Customizing the AI Prompt

```php
add_filter( 'air_prompt', function( $prompt, $max_keywords, $set_alt ) {
    // Use a custom multilingual prompt
    return "Analyze this image. Return {$max_keywords} descriptive keywords in English and German, separated by commas.";
}, 10, 3 );
```

### Adding Custom Settings

```php
// Add default value
add_filter( 'air_settings_defaults', function( $defaults ) {
    $defaults['pro_language'] = 'en';
    return $defaults;
});

// Register the field
add_action( 'air_register_settings_fields', function( $page_slug ) {
    add_settings_field(
        'pro_language',
        'Output Language',
        'render_language_field',
        $page_slug,
        'air_advanced_section'
    );
});

// Sanitize
add_filter( 'air_sanitize_settings', function( $sanitized, $input, $old ) {
    if ( isset( $input['pro_language'] ) ) {
        $sanitized['pro_language'] = sanitize_text_field( $input['pro_language'] );
    }
    return $sanitized;
}, 10, 3 );
```

### Overriding Twig Templates

```php
// Prepend Pro plugin views directory (Pro templates take precedence)
add_filter( 'air_template_paths', function( $paths ) {
    array_unshift( $paths, MY_PRO_PLUGIN_DIR . 'views' );
    return $paths;
});
```

Place your override templates at the same relative path:
- Override `admin/fields/model.twig` → `your-pro-plugin/views/admin/fields/model.twig`

### Adding File Type Support

```php
// Add AVIF to UI checkboxes
add_filter( 'air_available_file_types', function( $types ) {
    $types['image/avif'] = 'AVIF';
    return $types;
});

// Allow AVIF in validation
add_filter( 'air_allowed_file_types_for_validation', function( $types ) {
    $types[] = 'image/avif';
    return $types;
});

// Allow AVIF in Groq service
add_filter( 'air_allowed_file_types', function( $types ) {
    $types[] = 'image/avif';
    return $types;
});
```

### Modifying Filenames

```php
add_filter( 'air_new_filename', function( $filename, $sanitized, $ext, $file, $desc ) {
    // Add product SKU prefix
    $sku = get_current_product_sku();
    if ( $sku ) {
        return $sku . '-' . $filename;
    }
    return $filename;
}, 10, 5 );
```

### Logging API Responses

```php
add_action( 'air_api_response', function( $decoded, $code, $image_path ) {
    if ( 200 !== $code ) {
        error_log( "AIR API Error [{$code}]: " . print_r( $decoded, true ) );
    }
}, 10, 3 );
```

---

## Pro Plugin Bootstrap Example

```php
<?php
/**
 * Plugin Name: AI Image Renamer Pro
 * Requires Plugins: ai-image-renamer
 */

namespace AIR_Pro;

class Pro {
    public function __construct() {
        add_action( 'air_loaded', [ $this, 'init' ] );
    }

    public function init( $plugin ) {
        // Access core services
        $groq = $plugin->get_groq_service();
        $settings = $plugin->get_settings_page();

        // Register Pro features
        $this->register_filters();
        $this->register_settings();
    }

    private function register_filters() {
        add_filter( 'air_available_models', [ $this, 'add_pro_models' ] );
        add_filter( 'air_valid_models', [ $this, 'add_pro_model_ids' ] );
        add_filter( 'air_prompt', [ $this, 'enhance_prompt' ], 10, 3 );
        add_filter( 'air_template_paths', [ $this, 'add_pro_templates' ] );
    }

    private function register_settings() {
        add_filter( 'air_settings_defaults', [ $this, 'add_defaults' ] );
        add_action( 'air_register_settings_fields', [ $this, 'add_fields' ] );
        add_filter( 'air_sanitize_settings', [ $this, 'sanitize' ], 10, 3 );
    }
}

new Pro();
```

---

## Accessing Plugin Instance

```php
$plugin = \AIR\Plugin::get_instance();

// Available getters
$plugin->get_encryption_service();
$plugin->get_template_engine();
$plugin->get_groq_service();
$plugin->get_settings_page();
$plugin->get_image_uploader();
$plugin->is_pro_active();
```
