<?php

namespace App;

use App\View\Renderer;

require_once __DIR__ . '/vendor/autoload.php';

// Initialize SessionManager for secure session handling and CSRF protection
$sessionManager = new SessionManager();

// Add security headers
header( "X-Content-Type-Options: nosniff" );
header( "X-Frame-Options: DENY" );
header( "X-XSS-Protection: 1; mode=block" );

$app = new App();
$pageTitle = 'Article Editor';
$request = new Request( $sessionManager );
$view = new Renderer( $request, $sessionManager );

// Improved head section with safer script and stylesheet inclusion.
echo $view->renderHeader( $pageTitle );

// Display header and word count.
echo $view->renderContent( $app );

$view->renderListOfArticles( $app );

// Handle form submission with sanitization.
if ( $request->isPost() ) {
	$request->handleFormSubmission( $app );
}
