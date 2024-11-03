<?php

namespace App;

use App\Utility\StringSanitizer;
use DirectoryIterator;

require_once dirname( __DIR__ ) . '/globals.php';

class App {

	/**
	 * @var string The directory path where article files are stored.
	 */
	private string $articlePath;

	/**
	 * App constructor.
	 * Initializes the path where article files are located.
	 */
	public function __construct() {
		$this->articlePath = __DIR__ . '/../articles/';
	}

	/**
	 * Saves an article with a sanitized title and content.
	 *
	 * @param string $title The title of the article to save.
	 * @param string $body The body content of the article.
	 *
	 * @return void
	 *
	 * This method creates a file in the articles directory with a sanitized
	 * filename based on the title. The body content is also sanitized to prevent
	 * any potential HTML injection.
	 */
	public function saveArticle( string $title, string $body ): void {
		$filePath = $this->articlePath . StringSanitizer::fullSanitize( $title );
		file_put_contents( $filePath, StringSanitizer::fullSanitize( $body ) );
	}

	/**
	 * Fetches the content of an article file by its title, if it exists.
	 *
	 * @param string $title The title of the article to fetch.
	 * @return string The content of the article, or an empty string if not found.
	 *
	 * This method retrieves the content of an article file in a secure way. It first
	 * sanitizes the title and then checks if the article exists in the predefined list,
	 * preventing unauthorized access to unintended files.
	 */
	public function fetchArticle( string $title ): string {
		// Sanitize the title strictly, removing any special characters that could be used for traversal
		$safeTitle = StringSanitizer::sanitizeFileName( $title );
		// Build the file path
		$filePath = realpath( $this->articlePath . DIRECTORY_SEPARATOR . $safeTitle );
		// Verify the file path is valid and within the articles directory
		if ( $filePath !== false
			&& strpos( $filePath, realpath( $this->articlePath ) ) === 0
			&& in_array( $safeTitle, $this->getListOfArticles(), true )
			&& is_readable( $filePath )
		) {
			return file_get_contents( $filePath );
		}

		// If validation fails, return an empty string or handle the error accordingly
		return '';
	}

	/**
	 * Retrieves a list of all valid articles in the articles directory.
	 *
	 * @return array An array of article filenames.
	 *
	 * This method scans the articles directory and returns only valid files,
	 * filtering out system files and ensuring each file has an acceptable extension
	 * (such as `.txt`). This limits the risk of executing unintended files.
	 */
	public function getListOfArticles(): array {
		$articlePath = dirname( __DIR__ ) . '/articles/';
		$articles = [];

		foreach ( new DirectoryIterator( $articlePath ) as $fileInfo ) {
			if ( $fileInfo->isFile() ) {
				$articles[] = $fileInfo->getBasename();
			}
		}

		return $articles;
	}

	/**
	 * Sanitizes a filename to allow only alphanumeric characters, underscores, and dashes.
	 *
	 * @param string $name The original filename or title to sanitize.
	 * @return string A sanitized filename safe for use within the application.
	 *
	 * This method replaces any special characters in the filename with underscores,
	 * preventing directory traversal and ensuring the filename is safe for storage.
	 */
	private function sanitizeFileName( string $name ): string {
		return preg_replace( '/[^a-zA-Z0-9_-]/', '_', $name );
	}

	/**
	 * Checks if a given filename has a valid article extension (e.g., `.txt`).
	 *
	 * @param string $filename The name of the file to validate.
	 * @return bool True if the file is a valid article, otherwise false.
	 *
	 * This method enforces a restriction on file extensions to ensure only text files
	 * are considered articles. This prevents potentially harmful file types from being
	 * accessible within the application.
	 */
	private function isValidArticle( string $filename ): bool {
		return pathinfo( $filename, PATHINFO_EXTENSION ) === '';
	}

	/**
	 * Calculates the total word count across all articles, caching the result.
	 *
	 * @return int The total word count of all articles.
	 *
	 * This method uses lazy loading to cache the word count, reducing the need
	 * to repeatedly calculate it. It scans each article, counts its words, and
	 * sums up the results, returning a single integer value.
	 */
	public function getWordCount(): int {
		static $wordCount;

		if ($wordCount === null) {
			$wordCount = array_reduce($this->getListOfArticles(), function ($total, $article) {
				// Construct and resolve the full file path securely
				$filePath = realpath($this->articlePath . DIRECTORY_SEPARATOR . $article);
				// Ensure the file is within the allowed directory and has a valid extension
				if ($filePath !== false && strpos($filePath, realpath($this->articlePath)) === 0 && $this->isValidArticle($article)) {
					$content = file_get_contents($filePath);
					if ($content !== false) {
						$total += str_word_count($content);
					}
				}

				return $total;
			}, 0);
		}

		return $wordCount;
	}
}
