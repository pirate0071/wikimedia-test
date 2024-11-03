<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * @coversDefaultClass App
 */
class SecurityTest extends \PHPUnit\Framework\TestCase {

	/** @var Client */
	private Client $client;

	protected function setUp(): void {
		// Initialize Guzzle client to make HTTP requests to localhost
		$this->client = new Client( [ 'base_uri' => 'http://localhost:8989/' ] );
	}

	/**
	 * Test that input is sanitized to prevent XSS attacks.
	 */
	public function testInputSanitization() {
		// Simulate potentially unsafe input
		$response = $this->client->get( '/index.php', [
			'query' => [ 'title' => '<script>alert("xss")</script>' ]
		] );

		$body = (string)$response->getBody();

		// Assert that no script tags are present in the response
		$this->assertStringNotContainsString( '<script>', $body, 'Input should be sanitized to prevent XSS.' );
	}

	/**
	 * Test that output is properly escaped to prevent XSS attacks.
	 * @throws GuzzleException
	 */
	public function testOutputEscaping() {
		// Test with input that should be displayed on the page
		$response = $this->client->get( '/index.php', [
			'query' => [ 'title' => '<script>alert("xss")</script>' ]
		] );

		$body = (string)$response->getBody();

		// Assert that potentially dangerous tags are not present
		$this->assertStringNotContainsString( '<script>', $body, 'Output should be escaped to prevent XSS.' );
	}

	/**
	 * Test that CSRF token is present in the form.
	 * @throws GuzzleException
	 */
	public function testCsrfTokenPresence() {
		// Fetch the form page
		$response = $this->client->get( '/index.php' );
		$body = (string)$response->getBody();

		// Check for a CSRF token in the form
		$this->assertMatchesRegularExpression( '/<input type="hidden" name="csrf_token" value="[^"]+">/', $body, 'CSRF token should be present in form.' );
	}

	/**
	 * Test that file paths are validated to prevent directory traversal.
	 * @throws GuzzleException
	 */
	public function testFilePathValidation() {
		// Test with an invalid path attempting directory traversal
		$response = $this->client->get( '/index.php', [
			'query' => [ 'title' => '../etc/passwd' ]
		] );

		$body = (string)$response->getBody();

		// Assert that the file content is not displayed
		$this->assertStringNotContainsString( 'root:', $body, 'Directory traversal should be prevented.' );
	}

	/**
	 * Test that essential security headers are set.
	 * @throws GuzzleException
	 */
	public function testSecureHeaders() {
		// Make a request to the main page
		$response = $this->client->get( '/index.php' );

		// Assert that essential security headers are present
		$this->assertTrue( $response->hasHeader( 'Content-Security-Policy' ), 'Content-Security-Policy header should be set.' );
		$this->assertTrue( $response->hasHeader( 'X-Content-Type-Options' ), 'X-Content-Type-Options header should be set.' );
		$this->assertTrue( $response->hasHeader( 'X-Frame-Options' ), 'X-Frame-Options header should be set.' );

		// Check specific header values
		$this->assertEquals( "default-src 'self'", $response->getHeaderLine( 'Content-Security-Policy' ), 'Content-Security-Policy should restrict sources to self.' );
		$this->assertEquals( 'nosniff', $response->getHeaderLine( 'X-Content-Type-Options' ), 'X-Content-Type-Options should be nosniff.' );
		$this->assertEquals( 'DENY', $response->getHeaderLine( 'X-Frame-Options' ), 'X-Frame-Options should be DENY.' );
	}
}
