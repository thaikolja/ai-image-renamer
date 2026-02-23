# Action Hooks

AI Image Renamer provides 5 action hooks that allow developers to execute custom code at specific points during the plugin's lifecycle.

## Initialization

### `air_init`
Fires at the end of the plugin initialization process.

This hook is useful for registering custom components or overriding default behaviors right after the plugin itself has loaded.

**Parameters:**
- `Plugin $plugin`: The plugin singleton instance.

**Example:**
```php
add_action( 'air_init', function( $plugin ) {
    // Add custom logging when the plugin finishes loading.
}, 10, 1 );
```
**Source:** [`Plugin::init()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Plugin.php)

### `air_admin_init`
Fires when the admin component is initialized.

Use this hook to add custom admin pages, override settings, or enqueue specific scripts tailored for the AI Image Renamer backend.

**Parameters:**
- `Admin $admin`: The Admin component instance.

**Example:**
```php
add_action( 'air_admin_init', function( $admin ) {
    // Register custom scripts or styles for the settings page.
}, 10, 1 );
```
**Source:** [`Plugin::init()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Plugin.php)

## API Communication

### `air_api_response`
Fires immediately after a response is received from the Groq API.

Perfect for logging request/response times, debugging API payloads, or building analytics tools on top of the renamer.

**Parameters:**
- `$response` (`array|WP_Error`): The raw object returned by `wp_remote_post()`.
- `$payload` (`array`): The JSON payload that was sent to the API.

**Example:**
```php
add_action( 'air_api_response', function( $response, $payload ) {
    if ( is_wp_error( $response ) ) {
        error_log( 'Groq API Failed: ' . $response->get_error_message() );
    } else {
        $body = wp_remote_retrieve_body( $response );
        error_log( 'API Body: ' . $body );
    }
}, 10, 2 );
```
**Source:** [`Groq_Service::generate_description()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Services/Groq_Service.php)

## File Processing

### `air_file_renamed`
Fires after an uploaded file has been successfully renamed using AI keywords.

Useful for updating custom database tables, triggering third-party webhooks, or processing the new image further (e.g., specific watermarking).

**Parameters:**
- `$new_path` (`string`): The absolute path to the newly renamed file on the server.
- `$original_name` (`string`): The original filename before being renamed.

**Example:**
```php
add_action( 'air_file_renamed', function( $new_path, $original_name ) {
    // Example: Notify a slack webhook that an image was processed.
}, 10, 2 );
```
**Source:** [`Image_Uploader::process_upload()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Hooks/Image_Uploader.php)

## Settings & Admin Area

### `air_settings_fields`
Fires inside the `<form>` tag on the main settings page.

Allows developers to insert custom settings fields or entire sections directly into the AI Image Renamer settings page. This works well in combination with `air_settings_sanitize_{$section}`.

**Parameters:**
- `$options` (`array`): The currently saved plugin options.

**Example:**
```php
add_action( 'air_settings_fields', function( $options ) {
    echo '<h3>Custom Pro Features</h3>';
    echo '<p><label>Feature Toggle: <input type="checkbox" name="air_options[pro_toggle]" value="1"' . checked( 1, $options['pro_toggle'] ?? 0, false ) . '/></label></p>';
} );
```
**Source:** [`Settings_Page::register_settings()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Admin/Settings_Page.php)
