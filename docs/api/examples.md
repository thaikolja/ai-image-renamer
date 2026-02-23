# Examples

The following are real-world examples showing how to extend and customize **AI Image Renamer** using its built-in hooks.

## 1. Skipping AI for Specific Users

If you don't wanted admins or specific roles burning your API tokens, you can skip processing entirely.

```php
add_filter( 'air_should_process_upload', function( $should_process ) {
    if ( current_user_can( 'administrator' ) ) {
        return false; // Admins upload images with original names
    }
    return $should_process;
} );
```

## 2. Prefetching Brand Names into the Filename

Always append your company brand name (e.g., `acme-corp`) to the beginning of the renamed file.

```php
add_filter( 'air_sanitized_filename', function( $sanitized_slug, $original_name ) {
    return 'acme-corp-' . $sanitized_slug;
}, 10, 2 );
```

## 3. Disabling WebP & AVIF Support

By default, the plugin processes JPEGs, PNGs, AVIFs, and WebPs. If you only want it to work on standard JPGs:

```php
add_filter( 'air_allowed_file_types', function( $file_types ) {
    return [ 'image/jpeg', 'image/jpg' ]; // Strip out png/webp/avif
} );
```

## 4. Prompt Engineering: Forcing Language

Force the AI to analyze and generate keywords in a different language (e.g., German instead of English).

```php
add_filter( 'air_prompt', function( $prompt, $max_keywords ) {
    return "Beschreibe dieses Bild in MAXIMAL $max_keywords Wörtern. Antworte AUSSCHLIESSLICH auf Deutsch. Sei prägnant.";
}, 10, 2 );
```

## 5. Adding Custom Admin Settings Tab

You can inject your own settings directly into the plugin's UI array, and hook into the sanitizer to save them.

```php
// 1. Output the HTML for the setting
add_action( 'air_settings_fields', function( $options ) {
    $custom_val = esc_attr( $options['custom_field'] ?? '' );
    ?>
    <hr>
    <h3>My Custom Settings</h3>
    <table class="form-table">
        <tr>
            <th scope="row">Feature Flag</th>
            <td><input type="text" name="air_options[custom_field]" value="<?php echo $custom_val; ?>"></td>
        </tr>
    </table>
    <?php
} );

// 2. Add 'custom_field' to the sanitization logic
add_filter( 'air_settings_sanitize_general', function( $sanitized, $raw_input ) {
    if ( isset( $raw_input['custom_field'] ) ) {
        $sanitized['custom_field'] = sanitize_text_field( $raw_input['custom_field'] );
    }
    return $sanitized;
}, 10, 2 );
```

## 6. Keeping the Original File Extension Intact

If you don't want the plugin trying to map `image/jpeg` to `jpg` and prefer whatever extension the user uploaded with:

```php
add_filter( 'air_file_extension', function( $extension, $mime_type ) {
    // If someone uploaded file.JPEG, keep it .JPEG
    $uploaded_ext = pathinfo( $_FILES['async-upload']['name'] ?? '', PATHINFO_EXTENSION );
    
    return $uploaded_ext ? strtolower( $uploaded_ext ) : $extension;
}, 10, 2 );
```

## 7. Logging Groq Vision API Requests

Keep a local text log of everything the plugin is sending to/receiving from the Groq API (useful for debugging prompt changes).

```php
add_action( 'air_api_response', function( $response, $payload ) {
    $log_file = WP_CONTENT_DIR . '/air-api.log';
    
    $log_data = [
        'time'    => current_time( 'mysql' ),
        'payload' => json_decode( $payload['body'], true ),
        'status'  => is_wp_error( $response ) ? 'ERROR' : wp_remote_retrieve_response_code( $response ),
    ];
    
    file_put_contents( $log_file, print_r( $log_data, true ), FILE_APPEND );
}, 10, 2 );
```

## 8. Forcing High Temperature (Creative Names)

Increase the "temperature" of the LLM to get more creative, random keywords instead of generic descriptive ones.

```php
add_filter( 'air_api_payload', function( $payload ) {
    $payload['temperature'] = 0.9; // Default is 1.0, lower is strict, higher is creative. Wait, 1.0 is creative. Let's make it 2.0.
    $payload['temperature'] = 1.8; 
    return $payload;
} );
```

## 9. Creating a Slack Webhook on Upload

Ping a Slack channel whenever an image is uploaded and automatically renamed.

```php
add_action( 'air_file_renamed', function( $new_path, $original_name ) {
    $webhook_url = 'https://hooks.slack.com/services/XXX/YYY/ZZZ';
    $new_name    = basename( $new_path );
    
    wp_remote_post( $webhook_url, [
        'body' => json_encode([
            'text' => "📸 New Image Processed! \nOriginal: `$original_name` \nNew Name: `$new_name`"
        ])
    ]);
}, 10, 2 );
```

## 10. Scrubbing Specific Words from Filenames

Remove the word "photo" or "picture" if the AI includes it in the raw description.

```php
add_filter( 'air_generated_description', function( $description ) {
    $banned = [ 'image of', 'photo of', 'picture of', 'photograph showing' ];
    return str_ireplace( $banned, '', $description );
} );
```

## 11. Changing the Target Model

Programmatically enforce a different Llama vision model, ignoring what's saved in the admin settings.

```php
add_filter( 'air_api_payload', function( $payload ) {
    // Force the massive 90B model instead of 11B/90B.
    $payload['model'] = 'llama-3.2-90b-vision-preview';
    return $payload;
} );
```
