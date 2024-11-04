<?php

namespace App;

use App\Utility\StringSanitizer;
use Random\RandomException;

/**
 * Manages session operations including initialization, CSRF protection, and CAPTCHA validation.
 */
class SessionManager {
	private const CSRF_TOKEN_KEY = 'csrf_token';
	private const TOKEN_EXPIRY_TIME = 1800; // 30 minutes

	/**
	 * Initializes the session with secure configurations and prevents session fixation attacks.
	 *
	 * @return void
	 */
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
	 * Validates the provided CAPTCHA answer by comparing it with stored
	 * server-side data after sanitizing the client-side input.
	 *
	 * @param string $captchaAnswer The CAPTCHA answer provided by the client.
	 *
	 * @return bool True if the CAPTCHA answer matches the stored value, false otherwise.
	 */
	public function validateCaptchaAnswer(string $captchaAnswer ): bool {
		$clientCaptchaAnswer = StringSanitizer::sanitizeFileName( $captchaAnswer );
		return $this->getByKey( 'captcha_answer' ) === $clientCaptchaAnswer;
	}

	/**
	 * Retrieve and sanitize a value from the session by its key.
	 *
	 * @param string $key The key to look up in the session.
	 * @return string The sanitized value associated with the given key.
	 */
	public function getByKey( string $key ): string {
		return StringSanitizer::fullSanitize( $_SESSION[$key] ?? '' );
	}

	/**
	 * Sets the user's answer to the CAPTCHA.
	 *
	 * @param string $captchaAnswer The answer provided by the user for the CAPTCHA.
	 * @return void
	 */
	public function setCaptchaAnswer(string $captchaAnswer ): void {
		$_SESSION['captcha_answer'] = $captchaAnswer;
	}

	/**
	 * Unsets the stored CAPTCHA answer from the session.
	 *
	 * @return void
	 */
	public function unsetCaptchaAnswer(): void {
		unset( $_SESSION['captcha_answer'] );
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
