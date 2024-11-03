<?php

namespace App\Utility;

use PDO;

class StringSanitizer {
	/**
	 * Escapes HTML entities to prevent XSS attacks.
	 *
	 * @param string $input The input string to be sanitized.
	 * @return string The sanitized string with HTML entities escaped.
	 */
	public static function escapeHtml( string $input ): string {
		return htmlspecialchars( $input, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Removes special characters from a filename, allowing only alphanumeric, dashes, and underscores.
	 * This helps prevent directory traversal and invalid filenames.
	 *
	 * @param string $filename The filename to be sanitized.
	 * @return string The sanitized filename.
	 */
	public static function sanitizeFileName( string $filename ): string {
		return preg_replace( '/[^a-zA-Z0-9-_]/', '', $filename );
	}

	/**
	 * Strips dangerous HTML tags and attributes to prevent script injection attacks.
	 *
	 * @param string $input The input string to be sanitized.
	 * @return string The sanitized string with only safe HTML tags.
	 */
	public static function stripDangerousTags( string $input ): string {
		$allowedTags = '<p><a><b><i><strong><em><ul><ol><li><br>';
		return strip_tags( $input, $allowedTags );
	}

	/**
	 * Sanitizes input for SQL queries by escaping special characters.
	 * This should be used in addition to prepared statements for full SQL injection prevention.
	 *
	 * @param string $input The input string to be sanitized for SQL.
	 * @param PDO $pdo The PDO instance for database connection (to handle character encoding).
	 * @return string The sanitized string safe for SQL queries.
	 */
	public static function sanitizeForSql( string $input, PDO $pdo ): string {
		return $pdo->quote( $input );
	}

	/**
	 * Limits the length of the input string to a specified maximum.
	 *
	 * @param string $input The input string to be sanitized.
	 * @param int $maxLength The maximum allowed length of the string.
	 * @return string The truncated string if it exceeds the maximum length.
	 */
	public static function limitLength( string $input, int $maxLength = 255 ): string {
		return mb_substr( $input, 0, $maxLength, 'UTF-8' );
	}

	/**
	 * Full sanitize function combining multiple sanitization steps.
	 * Useful for general input sanitization to ensure clean, safe output.
	 *
	 * @param string $input The input string to be fully sanitized.
	 * @param int $maxLength The maximum allowed length of the sanitized string.
	 * @return string The fully sanitized string.
	 */
	public static function fullSanitize( string $input, int $maxLength = 255 ): string {
		$input = self::stripDangerousTags( $input );  // Strip unsafe HTML tags
		$input = self::escapeHtml( $input );          // Escape HTML entities
		$input = self::limitLength( $input, $maxLength ); // Limit length
		return $input;
	}
}
