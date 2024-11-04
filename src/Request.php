<?php

namespace App;

use App\Utility\StringSanitizer;

class Request
{
	private SessionManager $sessionManager;

	public function __construct(?SessionManager $sessionManager = null)
	{
		if ($sessionManager) {
			$this->sessionManager = $sessionManager;
		}
	}

	/**
	 * Retrieve sanitized input data from a POST request.
	 *
	 * @param string $key The key to retrieve from POST data.
	 * @return string|null The sanitized input or null if not set.
	 */
	public function getPostData(string $key): ?string
	{
		// Check if key exists in $_POST and sanitize it.
		return isset($_POST[$key]) ? StringSanitizer::fullSanitize(trim($_POST[$key])) : null;
	}

	/**
	 * Checks if the current request is a POST request.
	 *
	 * @return bool True if the request method is POST, otherwise false.
	 */
	public function isPost(): bool
	{
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

	/**
	 * Handle form submission securely by sanitizing and validating input data.
	 *
	 * @param App $app An instance of the App class for handling article operations.
	 * @return bool True if the article was saved successfully, otherwise false.
	 */
	public function handleFormSubmission(App $app): bool
	{
		// Ensure the request is a POST request.
		if (!$this->isPost()) {
			return false;
		}

		// Ensure CSRF TOKEN valid
		if ($this->sessionManager->validateCsrfToken($this->getPostData('csrf_token')) === false) {
			return false;
		}

		// Retrieve sanitized title and body from POST data.
		$title = $this->getPostData('title');
		$body = $this->getPostData('body');

		// Validate that both title and body are present.
		if ($title && $body) {
			// Save the article through the App instance.
			$app->saveArticle($title, $body);
			return true;
		}

		return false; // Return false if required fields are missing.
	}

	public function getGetData(string $key): ?string
	{
		// Check if key exists in $_GET and sanitize it.
		return isset($_GET[$key]) ? StringSanitizer::fullSanitize(trim($_GET[$key])) : '';
	}
}
