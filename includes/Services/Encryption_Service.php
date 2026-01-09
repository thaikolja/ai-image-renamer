<?php

/**
 * Encryption Service for secure API key storage.
 *
 * @package AIR\Services
 */

declare(strict_types=1);

namespace AIR\Services;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;

/**
 * Class Encryption_Service
 *
 * Handles encryption and decryption of sensitive data using defuse/php-encryption.
 */
class Encryption_Service
{

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
    private ?Key $key = null;

    /**
     * Constructor.
     *
     * Initializes or loads the encryption key.
     */
    public function __construct()
    {
        $this->key = $this->get_or_create_key();
    }

    /**
     * Get or create the encryption key.
     *
     * The key is stored in wp-config.php as a constant if defined,
     * otherwise stored as a WordPress option (less secure but works out of the box).
     *
     * @return Key|null The encryption key or null on failure.
     */
    private function get_or_create_key(): ?Key
    {
        // Check if key is defined in wp-config.php (recommended).
        if (defined('AIR_ENCRYPTION_KEY') && ! empty(AIR_ENCRYPTION_KEY)) {
            try {
                return Key::loadFromAsciiSafeString(AIR_ENCRYPTION_KEY);
            } catch (\Exception $e) {
                // Invalid key format, fall through to option-based key.
                error_log('AI Image Renamer: Invalid encryption key constant. ' . $e->getMessage());
            }
        }

        // Check if key exists in options.
        $stored_key = get_option(self::KEY_OPTION_NAME);

        if ($stored_key) {
            try {
                return Key::loadFromAsciiSafeString($stored_key);
            } catch (\Exception $e) {
                // Corrupted key, regenerate.
                error_log('AI Image Renamer: Corrupted encryption key option. Regenerating.');
                delete_option(self::KEY_OPTION_NAME);
            }
        }

        // Generate a new key and store it.
        try {
            $new_key    = Key::createNewRandomKey();
            $key_string = $new_key->saveToAsciiSafeString();

            // Store in options (autoloaded for performance).
            update_option(self::KEY_OPTION_NAME, $key_string, false);

            return $new_key;
        } catch (\Exception $e) {
            error_log('AI Image Renamer: Failed to create encryption key. ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Encrypt a string.
     *
     * @param string $plaintext The data to encrypt.
     *
     * @return string|false The encrypted ciphertext or false on failure.
     */
    public function encrypt(string $plaintext): string|false
    {
        if (null === $this->key || empty($plaintext)) {
            return false;
        }

        try {
            return Crypto::encrypt($plaintext, $this->key);
        } catch (\Exception $e) {
            error_log('AI Image Renamer: Encryption failed. ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrypt a ciphertext string.
     *
     * @param string $ciphertext The encrypted data.
     *
     * @return string|false The decrypted plaintext or false on failure.
     */
    public function decrypt(string $ciphertext): string|false
    {
        if (null === $this->key || empty($ciphertext)) {
            return false;
        }

        try {
            return Crypto::decrypt($ciphertext, $this->key);
        } catch (WrongKeyOrModifiedCiphertextException $e) {
            error_log('AI Image Renamer: Decryption failed (wrong key or tampered data). ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('AI Image Renamer: Decryption failed. ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if the encryption service is functional.
     *
     * @return bool True if encryption is available.
     */
    public function is_available(): bool
    {
        return null !== $this->key;
    }
}
