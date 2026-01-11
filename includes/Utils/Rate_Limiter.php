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
 * Rate Limiter Utility.
 *
 * @package AIR\Utils
 */

declare( strict_types=1 );

namespace AIR\Utils;

/**
 * Class Rate_Limiter
 *
 * Provides rate limiting functionality to prevent abuse of AJAX endpoints.
 */
class Rate_Limiter {

	/**
	 * Default rate limit: 30 requests per minute.
	 *
	 * @var int
	 */
	private const DEFAULT_MAX_REQUESTS = 999999;

	/**
	 * Default time window: 60 seconds.
	 *
	 * @var int
	 */
	private const DEFAULT_TIME_WINDOW = 60;

	/**
	 * Transient prefix for rate limit storage.
	 *
	 * @var string
	 */
	private const TRANSIENT_PREFIX = 'air_rate_limit_';

	/**
	 * Check if a request should be rate limited.
	 *
	 * @param  string  $action        The action being performed.
	 * @param  int     $max_requests  Maximum number of requests allowed.
	 * @param  int     $time_window   Time window in seconds.
	 *
	 * @return bool True if the request is allowed, false if rate limited.
	 */
	public static function check_rate_limit(
		string $action, int $max_requests = self::DEFAULT_MAX_REQUESTS, int $time_window = self::DEFAULT_TIME_WINDOW
	): bool {
		$user_id = self::get_user_identifier();
		$key     = self::get_transient_key( $action, $user_id );

		// Get current request count.
		$data = \get_transient( $key );

		if ( false === $data ) {
			// First request in this window.
			$data = [
				'count'   => 1,
				'expires' => \time() + $time_window,
				'window'  => $time_window,
			];

			\set_transient( $key, $data, $time_window );

			return true;
		}

		// Check if the window has expired.
		if ( \time() > $data[ 'expires' ] ) {
			// Reset the counter.
			$data = [
				'count'   => 1,
				'expires' => \time() + $time_window,
				'window'  => $time_window,
			];

			\set_transient( $key, $data, $time_window );

			return true;
		}

		// Check if the limit has been exceeded.
		if ( $data[ 'count' ] >= $max_requests ) {
			return false;
		}

		// Increment the counter.
		$data[ 'count' ] ++;
		$remaining_time = $data[ 'expires' ] - \time();

		\set_transient( $key, $data, $remaining_time );

		return true;
	}

	/**
	 * Get the number of remaining requests for the current user.
	 *
	 * @param  string  $action        The action being performed.
	 * @param  int     $max_requests  Maximum number of requests allowed.
	 * @param  int     $time_window   Time window in seconds.
	 *
	 * @return int Number of remaining requests.
	 */
	public static function get_remaining_requests(
		string $action, int $max_requests = self::DEFAULT_MAX_REQUESTS, int $time_window = self::DEFAULT_TIME_WINDOW
	): int {
		$user_id = self::get_user_identifier();
		$key     = self::get_transient_key( $action, $user_id );

		$data = \get_transient( $key );

		if ( false === $data ) {
			return $max_requests;
		}

		// Check if the window has expired.
		if ( \time() > $data[ 'expires' ] ) {
			return $max_requests;
		}

		return max( 0, $max_requests - $data[ 'count' ] );
	}

	/**
	 * Get the time until the rate limit resets.
	 *
	 * @param  string  $action  The action being performed.
	 *
	 * @return int Seconds until reset, or 0 if not limited.
	 */
	public static function get_reset_time( string $action ): int {
		$user_id = self::get_user_identifier();
		$key     = self::get_transient_key( $action, $user_id );

		$data = \get_transient( $key );

		if ( false === $data ) {
			return 0;
		}

		$remaining = $data[ 'expires' ] - \time();

		return max( 0, $remaining );
	}

	/**
	 * Reset the rate limit for a specific action.
	 *
	 * @param  string  $action  The action to reset.
	 *
	 * @return void
	 */
	public static function reset_rate_limit( string $action ): void {
		$user_id = self::get_user_identifier();
		$key     = self::get_transient_key( $action, $user_id );

		\delete_transient( $key );
	}

	/**
	 * Get a unique identifier for the current user.
	 *
	 * @return string User identifier.
	 */
	private static function get_user_identifier(): string {
		// Use user ID if logged in.
		if ( \is_user_logged_in() ) {
			return 'user_' . \get_current_user_id();
		}

		// Use IP address for non-logged-in users.
		$ip = self::get_client_ip();

		return 'ip_' . ( $ip ?: 'unknown' );
	}

	/**
	 * Get the client IP address.
	 *
	 * @return string|null IP address or null if not available.
	 */
	private static function get_client_ip(): ?string {
		$ip_keys = [
			'HTTP_CF_CONNECTING_IP', // Cloudflare
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		];

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = \sanitize_text_field( \wp_unslash( $_SERVER[ $key ] ) );

				// Handle multiple IPs in X-Forwarded-For.
				if ( str_contains( $ip, ',' ) ) {
					$ips = explode( ',', $ip );
					$ip  = \trim( $ips[ 0 ] );
				}

				// Validate IP format.
				if ( \filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return null;
	}

	/**
	 * Generate the transient key for rate limiting.
	 *
	 * @param  string  $action   The action.
	 * @param  string  $user_id  The user identifier.
	 *
	 * @return string The transient key.
	 */
	private static function get_transient_key( string $action, string $user_id ): string {
		return self::TRANSIENT_PREFIX . \sanitize_key( $action ) . '_' . \md5( $user_id );
	}

	/**
	 * Send a rate limit error response.
	 *
	 * @param  string  $action  The action being performed.
	 *
	 * @return void
	 */
	public static function send_rate_limit_error( string $action ): void {
		$remaining_time = self::get_reset_time( $action );

		\wp_send_json_error( [
			'message'     => \__( 'Rate limit exceeded. Please try again later.', 'ai-image-renamer' ),
			'retry_after' => $remaining_time,
		] );
	}
}
