<?php

namespace Tests;

use App\SessionManager;
use App\Utility\StringSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * A set of tests for the SessionManager class from the App namespace.
 *
 * These tests focus specifically on the getCsrfToken method of the SessionManager class.
 */
class SessionManagerTest extends TestCase
{
    /** @var SessionManager */
    private $sessionManager;


    protected function setUp(): void
    {
        $this->sessionManager = new SessionManager();
    }

	/**
	 * @runInSeparateProcess
	 * Test that getCsrfToken returns a string.
	 *
	 * @throws \Random\RandomException
	 */
    public function testGetCsrfTokenReturnsString(): void
    {
        $csrfToken = $this->sessionManager->getCsrfToken();
        $this->assertIsString($csrfToken);
    }

	/**
	 * @runInSeparateProcess
	 * Test that getCsrfToken returns a sanitized string.
	 *
	 * @throws \Random\RandomException
	 */
    public function testGetCsrfTokenReturnsSanitizedString(): void
    {
        $csrfToken = $this->sessionManager->getCsrfToken();
        $this->assertEquals($csrfToken, StringSanitizer::escapeHtml($csrfToken));
    }

	/**
	 * @runInSeparateProcess
	 * Test that getCsrfToken generates new token when called twice with time delay.
	 *
	 * @throws \Random\RandomException
	 */
    public function testGetCsrfTokenGeneratesNewTokenWithTimeDelay(): void
    {
        $this->sessionManager->setTokenLifeTime(2);
		$csrfToken1 = $this->sessionManager->getCsrfToken();
        sleep(3); // Wait for token to expire
        $csrfToken2 = $this->sessionManager->getCsrfToken();
        $this->assertNotEquals($csrfToken1, $csrfToken2);
    }
}
