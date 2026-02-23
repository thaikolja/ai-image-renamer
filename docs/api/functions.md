# Functions

The AI Image Renamer plugin uses a modular, object-oriented structure. While it primarily relies on WordPress hooks to function, developers can interact with its public methods if needed.

## `Plugin`

The main bootstrap class that initializes all services and hooks.

### `get_instance()`

Returns the singleton instance of the plugin.

**Return:**
(`Plugin`) The singleton instance.

**Example:**
```php
$plugin = \AIR\Plugin::get_instance();
```

### `get_groq_service()`

Returns the instantiated Groq API service.

**Return:**
(`Groq_Service`) The Groq service instance.

**Example:**
```php
$groq = \AIR\Plugin::get_instance()->get_groq_service();
```

### `get_encryption_service()`

Returns the instantiated Encryption service.

**Return:**
(`Encryption_Service`) The Encryption service instance.

**Example:**
```php
$encryption = \AIR\Plugin::get_instance()->get_encryption_service();
```

## `Groq_Service`

Handles all communication with the Groq Vision API.

### `generate_description( string $tmp_path )`

Takes an image file and asks the Groq API to describe it.

**Parameters:**
- `$tmp_path` (`string`): The absolute path to the image file.

**Return:**
(`string|false`) The generated description, or `false` on failure.

**Example:**
```php
$groq = \AIR\Plugin::get_instance()->get_groq_service();
$description = $groq->generate_description( '/tmp/php8x7a.tmp' );
```

### `is_configured()`

Checks if the Groq API key has been entered and saved in settings.

**Return:**
(`bool`) True if an API key exists, false otherwise.

### `is_allowed_type( string $mime_type )`

Checks if a specific MIME type is allowed to be processed based on the plugin settings.

**Parameters:**
- `$mime_type` (`string`): The MIME type to check (e.g., `image/jpeg`).

**Return:**
(`bool`) True if allowed, false otherwise.

## `Encryption_Service`

Handles the encryption and decryption of sensitive data, specifically the Groq API key.

### `encrypt( string $plaintext )`

Encrypts a string using the defuse/php-encryption library.

**Parameters:**
- `$plaintext` (`string`): The string to encrypt.

**Return:**
(`string|false`) The encrypted string (ciphertext), or `false` on failure.

**Example:**
```php
$encryption = \AIR\Plugin::get_instance()->get_encryption_service();
$encrypted_key = $encryption->encrypt( 'gsk_123456789' );
```

### `decrypt( string $ciphertext )`

Decrypts a string that was previously encrypted by this service.

**Parameters:**
- `$ciphertext` (`string`): The encrypted string to decrypt.

**Return:**
(`string|false`) The decrypted plaintext string, or `false` on failure.

**Example:**
```php
$encryption = \AIR\Plugin::get_instance()->get_encryption_service();
$api_key = $encryption->decrypt( $encrypted_key );
```

### `is_usable()`

Checks if the encryption service is ready to use (i.e., a valid key has been loaded).

**Return:**
(`bool`) True if usable, false otherwise.

## `File_Sanitizer`

A utility class for cleaning up generated descriptions into valid filenames.

### `sanitize( string $description )`

Converts a raw string into a clean, lowercase, hyphenated slug suitable for a filename, removing any double hyphens or trailing punctuation.

**Parameters:**
- `$description` (`string`): The raw string to sanitize.

**Return:**
(`string`) The sanitized filename slug.

**Example:**
```php
$raw = "A golden retriever playing fetch in a sunny park.";
$clean = \AIR\Utils\File_Sanitizer::sanitize( $raw );
// Result: 'golden-retriever-playing-fetch-in-sunny-park'
```

## `Template_Engine`

Handles the rendering of Twig templates for the WordPress admin area.

### `render( string $template, array $context = [] )`

Renders a Twig template file with the provided context variables.

**Parameters:**
- `$template` (`string`): The path to the template file relative to the `templates` directory.
- `$context` (`array`): Optional. Variables to pass to the template.

**Return:**
(`string`) The rendered HTML.

**Example:**
```php
$engine = new \AIR\Services\Template_Engine();
echo $engine->render( 'admin/welcome.twig', [ 'version' => '1.0.0' ] );
```
