<?php

namespace App;

use App\Utility\StringSanitizer;
use Random\RandomException;

class SessionManager {
	private const CSRF_TOKEN_KEY = 'csrf_token';
	private const TOKEN_EXPIRY_TIME = 1800; // 30 minutes

	public function __construct() {
		// Configure session security settings
		session_set_cookie_params( [
			'lifetime' => 0,
			'path' => '/',
			'domain' => $_SERVER['SERVER_NAME'],
			'secure' => isset( $_SERVER['HTTPS'] ),
			'httponly' => true,
			'samesite' => 'Strict', // Prevents CSRF in cross-site contexts
		] );

		session_start();

		// Regenerate session ID periodically to prevent fixation attacks
		if ( !isset( $_SESSION['initiated'] ) ) {
			session_regenerate_id( true );
			$_SESSION['initiated'] = true;
		}
	}

	/**
	 * Generate or retrieve the CSRF token from the session.
	 *
	 * @return string The CSRF token.
	 * @throws RandomException
	 */
	public function getCsrfToken(): string {
		if ( !isset( $_SESSION[self::CSRF_TOKEN_KEY] ) || $this->isTokenExpired() ) {
			$_SESSION[self::CSRF_TOKEN_KEY] = bin2hex( random_bytes( 32 ) );
			$_SESSION[self::CSRF_TOKEN_KEY . '_time'] = time(); // Store creation time
		}

		return StringSanitizer::escapeHtml( $_SESSION[self::CSRF_TOKEN_KEY] );
	}

	/**
	 * Validate the CSRF token provided by the client.
	 *
	 * @param string $token The CSRF token to validate.
	 * @return bool True if the token is valid, false otherwise.
	 */
	public function validateCsrfToken( string $token ): bool {
		// Sanitize the input token before comparison
		$sanitizedToken = StringSanitizer::sanitizeFileName( $token );

		return isset( $_SESSION[self::CSRF_TOKEN_KEY] ) &&
			hash_equals( $_SESSION[self::CSRF_TOKEN_KEY], $sanitizedToken ) &&
			!$this->isTokenExpired();
	}

	/**
	 * Check if the CSRF token has expired.
	 *
	 * @return bool True if the token is expired, false otherwise.
	 */
	private function isTokenExpired(): bool {
		if ( !isset( $_SESSION[self::CSRF_TOKEN_KEY . '_time'] ) ) {
			return true;
		}

		return ( time() - $_SESSION[self::CSRF_TOKEN_KEY . '_time'] ) > self::TOKEN_EXPIRY_TIME;
	}

	/**
	 * Destroy the session, including CSRF token and other session data.
	 */
	public function destroySession(): void {
		$_SESSION = [];
		if ( ini_get( "session.use_cookies" ) ) {
			$params = session_get_cookie_params();
			setcookie( session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		session_destroy();
	}
}
