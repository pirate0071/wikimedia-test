<?php

namespace App;

use App\View\Renderer;

session_start();

require_once __DIR__ . '/vendor/autoload.php';

// Add security headers

header( "X-Content-Type-Options: nosniff" );
header( "X-Frame-Options: DENY" );
header( "X-XSS-Protection: 1; mode=block" );

$app = new App();
$pageTitle = 'Article Editor';
$view = new Renderer();
$request = new Request();
if ( !isset( $_SESSION['csrf_token'] ) || empty( $_SESSION['csrf_token'] ) ) {
	$_SESSION['csrf_token'] = bin2hex( random_bytes( 32 ) ); // Generate a token if none exists
}

// Improved head section with safer script and stylesheet inclusion.
echo $view->renderHeader( $pageTitle );

// Display header and word count.
echo $view->renderContent( $app );

$view->renderListOfArticles( $app );

// Handle form submission with sanitization.
$request->handleFormSubmission( $app, $_SESSION['csrf_token'] );
