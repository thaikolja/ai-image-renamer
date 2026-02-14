<?php
/**
 * AI Image Renamer.
 *
 * @description    Uses AI to rename images during upload for SEO-friendly filenames.
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
 * Encryption Service for secure API key storage.
 *
 * @package AIR\Services
 */

declare(strict_types=1);

namespace AIR\Services;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Exception;

/**
 * Class Encryption_Service
 *
 * Handles encryption and decryption of sensitive data using defuse/php-encryption.
 */
class Encryption_Service {


	/**
	 * Option name for storing the encryption key.
	 *
	 * @var string
	 */
	private const KEY_OPTION_NAME = 'air_encryption_key';

	/**
	 * The encryption key instance.
	 *
	 * @var Key|null
	 */
	private ?Key $key;

	/**
	 * Constructor.
	 *
	 * Initializes or loads the encryption key.
	 */
	public function __construct() {
		$this->key = $this->get_or_create_key();
	}

	/**
	 * Get or create the encryption key.
	 *
	 * The key is stored in wp-config.php as a constant if defined,
	 * otherwise stored as an encrypted WordPress option (less secure but works out of the box).
	 *
	 * @return Key|null The encryption key or null on failure.
	 */
	private function get_or_create_key(): ?Key {
		// Check if key is defined in wp-config.php (recommended).
		if ( \defined( 'AIR_ENCRYPTION_KEY' ) && ! empty( AIR_ENCRYPTION_KEY ) ) {
			try {
				return Key::loadFromAsciiSafeString( AIR_ENCRYPTION_KEY );
			} catch ( Exception $e ) {
				// Invalid key format, fall through to option-based key.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					\error_log( 'AI Image Renamer: Invalid encryption key constant. ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			}
		}

		// Check if key exists in options.
		$stored_key = \get_option( self::KEY_OPTION_NAME );

		if ( $stored_key ) {
			try {
				return Key::loadFromAsciiSafeString( $stored_key );
			} catch ( Exception ) {
				// Corrupted key, regenerate.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					\error_log( 'AI Image Renamer: Corrupted encryption key option. Regenerating.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}

				\delete_option( self::KEY_OPTION_NAME );
			}
		}

		// Generate a new key and store it.
		try {
			$new_key    = Key::createNewRandomKey();
			$key_string = $new_key->saveToAsciiSafeString();

			// Store in options (autoloaded for performance).
			\update_option( self::KEY_OPTION_NAME, $key_string, false );

			return $new_key;
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: Failed to create encryption key. ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return null;
		}
	}

	/**
	 * Encrypt a string.
	 *
	 * @param  string $plaintext  The data to encrypt.
	 *
	 * @return string|false The encrypted ciphertext or false on failure.
	 */
	final public function encrypt( string $plaintext ): string|false {
		if ( null === $this->key || empty( $plaintext ) ) {
			return false;
		}

		try {
			return Crypto::encrypt( $plaintext, $this->key );
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: Encryption failed. ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}
	}

	/**
	 * Decrypt a ciphertext string.
	 *
	 * @param  string $ciphertext  The encrypted data.
	 *
	 * @return string|false The decrypted plaintext or false on failure.
	 */
	final public function decrypt( string $ciphertext ): string|false {
		if ( null === $this->key || empty( $ciphertext ) ) {
			return false;
		}

		try {
			return Crypto::decrypt( $ciphertext, $this->key );
		} catch ( WrongKeyOrModifiedCiphertextException $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: Decryption failed (wrong key or tampered data). ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		} catch ( Exception $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				\error_log( 'AI Image Renamer: Decryption failed. ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}

			return false;
		}
	}

	/**
	 * Check if the encryption service is functional.
	 *
	 * @return bool True if encryption is available.
	 */
	final public function is_available(): bool {
		return null !== $this->key;
	}

	/**
	 * Check if the encryption key is stored in wp-config.php (recommended) or in options table.
	 *
	 * @return bool True if key is defined in wp-config.php, false if stored in options.
	 */
	final public function is_using_config_constant(): bool {
		return \defined( 'AIR_ENCRYPTION_KEY' ) && ! empty( AIR_ENCRYPTION_KEY );
	}

	/**
	 * Display admin notice if encryption key is stored in options instead of wp-config.php.
	 *
	 * @return void
	 */
	final public function maybe_show_security_notice(): void {
		if ( ! $this->is_available() ) {
			return;
		}

		if ( ! $this->is_using_config_constant() ) {
			$dismissed_key = 'air_encryption_notice_dismissed';
			if ( \get_user_meta( \get_current_user_id(), $dismissed_key, true ) ) {
				return;
			}

			?>
			<div class="notice notice-warning is-dismissible air-encryption-notice">
				<p>
					<strong><?php \esc_html_e( 'Security Warning: AI Image Renamer', 'ai-image-renamer' ); ?></strong><br>
					<?php
					\printf( /* translators: %s: AIR_ENCRYPTION_KEY */\esc_html__( 'Your encryption key is stored in the WordPress database. For better security, define %s in your wp-config.php file.', 'ai-image-renamer' ), '<code>AIR_ENCRYPTION_KEY</code>' );
					?>
				</p>
				<p>
					<a
						href="https://docs.kolja-nolte.com/ai-image-renamer/support/security"
						target="_blank">
						<?php \esc_html_e( 'Learn more about securing your encryption key', 'ai-image-renamer' ); ?>
					</a>
				</p>
			</div>
			<script>
				jQuery(document).ready(function($) {
					$('.air-encryption-notice').on('click', '.notice-dismiss', function() {
						jQuery.ajax({
							url: ajaxurl,
							method: 'POST',
							data: {
								action: 'air_dismiss_encryption_notice',
								nonce: '<?php echo \esc_attr( \wp_create_nonce( 'air_dismiss_encryption_notice' ) ); ?>'
							}
						});
					});
				});
			</script>
			<?php
		}
	}
}
