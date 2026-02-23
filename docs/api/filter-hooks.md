# Filter Hooks

AI Image Renamer provides 17 filter hooks that allow developers to modify data before it is processed, saved, or sent to the Groq API.

## API & Prompt Filters

### `air_prompt`
Filters the instruction prompt sent to the LLM.

**Parameters:**
- `$prompt` (`string`): The default prompt.
- `$max_keywords` (`int`): The maximum number of keywords configured in settings.

**Example:**
```php
add_filter( 'air_prompt', function( $prompt, $max_keywords ) {
    return "Describe this image in EXACTLY $max_keywords words. Focus only on the colors and the main subject.";
}, 10, 2 );
```
**Source:** [`Groq_Service::get_prompt()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Services/Groq_Service.php)

### `air_allowed_file_types`
Filters the list of MIME types allowed for AI processing.

**Parameters:**
- `$file_types` (`array`): Array of allowed MIME types.
- `$mime_type` (`string`): The specific MIME type currently being checked.

**Example:**
```php
add_filter( 'air_allowed_file_types', function( $file_types ) {
    $file_types[] = 'image/svg+xml'; // Warning: SVGs require a different vision approach.
    return $file_types;
} );
```
**Source:** [`Groq_Service::is_allowed_type()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Services/Groq_Service.php)

### `air_api_payload`
Filters the entire JSON payload right before it is sent to the Groq API.

**Parameters:**
- `$payload` (`array`): The complete API request payload.
- `$tmp_path` (`string`): Path to the temporary image file.

**Example:**
```php
add_filter( 'air_api_payload', function( $payload ) {
    $payload['temperature'] = 0.5; // Lower temperature for more deterministic results
    return $payload;
} );
```
**Source:** [`Groq_Service::generate_description()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Services/Groq_Service.php)

### `air_api_request_args`
Filters the WordPress `wp_remote_post` arguments.

**Parameters:**
- `$args` (`array`): The HTTP request arguments (headers, body, timeout).

**Example:**
```php
add_filter( 'air_api_request_args', function( $args ) {
    $args['timeout'] = 45; // Increase timeout to 45 seconds
    return $args;
} );
```
**Source:** [`Groq_Service::generate_description()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Services/Groq_Service.php)

### `air_generated_description`
Filters the final description returned by the LLM *before* it is sanitized.

**Parameters:**
- `$description` (`string`): The raw text returned by the AI.

**Example:**
```php
add_filter( 'air_generated_description', function( $description ) {
    return str_replace( 'photograph of', '', $description );
} );
```
**Source:** [`Groq_Service::generate_description()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Services/Groq_Service.php)

## Upload Processing Filters

### `air_should_process_upload`
Determines if an uploaded file should be processed by the AI.

**Parameters:**
- `$should_process` (`bool`): Whether to process (default: true).
- `$file` (`array`): The `$_FILES` array for the uploaded image.

**Example:**
```php
add_filter( 'air_should_process_upload', function( $should_process, $file ) {
    if ( strpos( $file['name'], 'no-ai-' ) === 0 ) {
        return false; // Skip images prefixed with 'no-ai-'
    }
    return $should_process;
}, 10, 2 );
```
**Source:** [`Image_Uploader::process_upload()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Hooks/Image_Uploader.php)

### `air_sanitized_filename`
Filters the sanitized, SEO-friendly slug *before* it is applied to the file.

**Parameters:**
- `$sanitized_name` (`string`): The cleaned filename slug.
- `$original_name` (`string`): The original filename.

**Example:**
```php
add_filter( 'air_sanitized_filename', function( $sanitized_name ) {
    return 'brand-prefix-' . $sanitized_name;
} );
```
**Source:** [`Image_Uploader::process_upload()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Hooks/Image_Uploader.php)

### `air_target_directory`
Filters the destination directory for the renamed file.

**Parameters:**
- `$target_dir` (`string`): The directory path (e.g., `/wp-content/uploads/2026/02`).

**Example:**
```php
// Advanced usage only. Ensure directory exists and is writable.
```
**Source:** [`Image_Uploader::process_upload()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Hooks/Image_Uploader.php)

### `air_final_filename`
Filters the complete, final filename (slug + extension) right before the old file is renamed.

**Parameters:**
- `$final_filename` (`string`): e.g., `golden-retriever.jpg`.
- `$sanitized_name` (`string`): e.g., `golden-retriever`.
- `$extension` (`string`): e.g., `jpg`.

**Example:**
```php
add_filter( 'air_final_filename', function( $final_filename, $sanitized_name, $extension ) {
    return $sanitized_name . '-' . time() . '.' . $extension;
}, 10, 3 );
```
**Source:** [`Image_Uploader::process_upload()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Hooks/Image_Uploader.php)

### `air_file_extension`
Filters the file extension determined from the MIME type.

**Parameters:**
- `$extension` (`string`): The determined extension (e.g., `jpg`).
- `$mime_type` (`string`): The source MIME type.

**Example:**
```php
add_filter( 'air_file_extension', function( $extension, $mime_type ) {
    if ( $mime_type === 'image/jpeg' ) {
        return 'jpeg'; // Use .jpeg instead of .jpg
    }
    return $extension;
}, 10, 2 );
```
**Source:** [`Image_Uploader::get_extension_from_mime()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Hooks/Image_Uploader.php)

## Settings & Admin Filters

### `air_twig_environment_args`
Filters the arguments passed to the Twig Environment constructor.

**Parameters:**
- `$args` (`array`): Twig environment options (cache, debug, auto_reload).

**Example:**
```php
add_filter( 'air_twig_environment_args', function( $args ) {
    $args['strict_variables'] = true;
    return $args;
} );
```
**Source:** [`Template_Engine::__construct()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Services/Template_Engine.php)

### `air_default_options`
Filters the default plugin settings.

**Parameters:**
- `$defaults` (`array`): Array containing default models, enabled state, etc.

**Example:**
```php
add_filter( 'air_default_options', function( $defaults ) {
    $defaults['enabled'] = false; // Default to Disabled on new installs
    $defaults['max_keywords'] = 3;
    return $defaults;
} );
```
**Source:** [`Settings_Page::get_defaults()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Admin/Settings_Page.php)

### `air_available_models`
Filters the list of available AI models shown in the dropdown.

**Parameters:**
- `$models` (`array`): Array of model keys mapping to their labels and descriptions.

**Example:**
```php
add_filter( 'air_available_models', function( $models ) {
    $models['llama-3.2-90b-vision-preview'] = [
        'label' => 'Llama 3.2 90B Vision',
        'desc'  => 'Experimental ultra-large vision model.',
    ];
    return $models;
} );
```
**Source:** [`Settings_Page::get_available_models()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Admin/Settings_Page.php)

### `air_settings_sections`
Allows adding custom panels/sections to the tabbed settings page.

**Parameters:**
- `$sections` (`array`): Array of section slugs.

**Example:**
```php
add_filter( 'air_settings_sections', function( $sections ) {
    $sections[] = 'pro_features';
    return $sections;
} );
```
**Source:** [`Settings_Page::sanitize_settings()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Admin/Settings_Page.php)

### `air_settings_sanitize_{$section}`
Dynamic filter to sanitize data for a specific settings section.

**Parameters:**
- `$sanitized` (`array`): The currently sanitized data.
- `$input` (`array`): The raw `$_POST` input for this section.
- `$old_options` (`array`): The previously saved options.

**Example:**
```php
add_filter( 'air_settings_sanitize_pro_features', function( $sanitized, $input ) {
    $sanitized['watermark'] = sanitize_text_field( $input['watermark'] ?? '' );
    return $sanitized;
}, 10, 2 );
```
**Source:** [`Settings_Page::sanitize_settings()`](https://gitlab.com/thaikolja/wp-ai-image-renamer/-/blob/main/includes/Admin/Settings_Page.php)
