<?php

namespace Tests\Utility;

use App\Utility\StringSanitizer;
use PHPUnit\Framework\TestCase;

class StringSanitizerTest extends TestCase {
	/**
	 * @var string Sample string with HTML tags and characters for testing.
	 */
	private string $input = "<p>Hello, 'World' & welcome!</p>";

	/**
	 * Test the escapeHtml method.
	 *
	 * Assert that the method correctly escapes HTML tags
	 * and characters to prevent XSS attacks.
	 */
	public function testEscapeHtml(): void {
		$sanitized = StringSanitizer::escapeHtml( $this->input );

		$this->assertSame( "&lt;p&gt;Hello, &#039;World&#039; &amp; welcome!&lt;/p&gt;", $sanitized );
	}

	/**
	 * Test the escapeHtml method with an empty string.
	 *
	 * Assert that the method returns an empty string when the input is an empty string.
	 */
	public function testEscapeHtmlWithEmptyString(): void {
		$sanitized = StringSanitizer::escapeHtml( "" );

		$this->assertSame( "", $sanitized );
	}

	/**
	 * Test the sanitizeFileName method.
	 *
	 * Assert that the method correctly removes special characters
	 * only allowing alphanumeric, dashes and underscores.
	 */
	public function testSanitizeFileName(): void {
		$sanitized = StringSanitizer::sanitizeFileName( "test@file.jpg!%" );

		$this->assertSame( "testfilejpg", $sanitized );
	}

	/**
	 * Test the sanitizeFileName method with an empty string.
	 *
	 * Assert that the method returns an empty string when the input is an empty string.
	 */
	public function testSanitizeFileNameWithEmptyString(): void {
		$sanitized = StringSanitizer::sanitizeFileName( "" );

		$this->assertSame( "", $sanitized );
	}

	/**
	 * Test the stripDangerousTags method with a string containing dangerous tags.
	 *
	 * Assert that the method correctly strips dangerous tags.
	 */
	public function testStripDangerousTagsWithDangerousTags(): void {
		$sanitized = StringSanitizer::stripDangerousTags( "<script>alert('hi')</script><p>Test</p>" );
		$this->assertSame( "alert('hi')<p>Test</p>", $sanitized );
	}

	/**
	 * Test the stripDangerousTags method with a string containing allowed tags.
	 *
	 * Assert that the method does not strip allowed/ safe tags.
	 */
	public function testStripDangerousTagsWithAllowedTags(): void {
		$sanitized = StringSanitizer::stripDangerousTags( "<p>Hello World!</p><a href='https://google.com'>Google</a>" );
		$this->assertSame( "<p>Hello World!</p><a href='https://google.com'>Google</a>", $sanitized );
	}

	/**
	 * Test the stripDangerousTags method with an empty string.
	 *
	 * Assert that the method returns an empty string when input is an empty string.
	 */
	public function testStripDangerousTagsWithEmptyString(): void {
		$sanitized = StringSanitizer::stripDangerousTags( "" );
		$this->assertSame( "", $sanitized );
	}

	/**
	 * Test the fullSanitize method with an empty string.
	 *
	 * Assert that the method returns an empty string when input is empty.
	 */
	public function testFullSanitizeWithEmptyString(): void {
		$sanitized = StringSanitizer::fullSanitize( "", 10 );
		$this->assertSame( "", $sanitized );
	}
}
