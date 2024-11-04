<?php

namespace App\Controller;

use App\App;
use App\Request;

class ApiController {
	private App $app;
	private ?array $articleListCache = null; // Cache for articles list

	private Request $request;

	/**
	 * ApiController constructor.
	 *
	 * @param App $app An instance of the App class.
	 */
	public function __construct( App $app ) {
		$this->app = $app;
		$this->request = new Request();
	}

	/**
	 * Main function to handle API requests and route them accordingly.
	 */
	public function handleRequest(): void {
		// Set JSON header for the response
		header( 'Content-Type: application/json' );

		// Retrieve sanitized request parameters
		$title = $this->request->getGetData( 'title' );
		$prefixSearch = $this->request->getGetData( 'prefixsearch' );

		// Route the request based on parameters
		if ( !$title && !$prefixSearch ) {
			$this->respondWithJson( [ 'content' => $this->getCachedListOfArticles() ] );
		} elseif ( $prefixSearch ) {
			$this->respondWithJson( [ 'content' => $this->performPrefixSearch( $prefixSearch ) ] );
		} elseif ( $title ) {
			$this->respondWithJson( [ 'content' => $this->app->fetchArticle( $title ) ] );
		} else {
			// Fallback response for unknown routes
			$this->respondWithJson( [ 'error' => 'Invalid request' ], 400 );
		}
	}

	/**
	 * Perform a prefix search on the list of articles.
	 *
	 * @param string $prefix The prefix to search for.
	 * @return array List of articles matching the prefix.
	 */
	private function performPrefixSearch( string $prefix ): array {
		// Filter articles with array_filter for optimized searching
		return array_values( array_filter(
			$this->getCachedListOfArticles(),
			fn( $article ) => stripos( $article, $prefix ) === 0
		) );
	}

	/**
	 * Get the list of articles with caching to improve performance.
	 *
	 * @return array List of article titles.
	 */
	private function getCachedListOfArticles(): array {
		// Check if article list is already cached
		if ( $this->articleListCache === null ) {
			$this->articleListCache = $this->app->getListOfArticles();
		}

		return $this->articleListCache;
	}

	/**
	 * Output a JSON response and set the HTTP status code.
	 *
	 * @param array $data The data to encode and return as JSON.
	 * @param int $statusCode HTTP status code for the response.
	 */
	private function respondWithJson( array $data, int $statusCode = 200 ): void {
		http_response_code( $statusCode );
		echo json_encode( $data );
		exit;
	}
}
