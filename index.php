<?php

namespace App;

use App\View\Renderer;
use DI\ContainerBuilder;

require_once __DIR__ . '/vendor/autoload.php';

// Create a DI container builder
$containerBuilder = new ContainerBuilder();

// Load the external DI configuration file
$diConfig = require __DIR__ . '/di-config.php';
$diConfig( $containerBuilder ); // Apply configurations

// Build the container
$container = $containerBuilder->build();

// Add security headers
header( "X-Content-Type-Options: nosniff" );
header( "X-Frame-Options: DENY" );
header( "X-XSS-Protection: 1; mode=block" );

// Get instances from the container
$app = $container->get( App::class );
// Get instances from the session manager
$sessionManager = $container->get( SessionManager::class );

$pageTitle = 'Article Editor';
$request = $container->get( Request::class );
$view = $container->get( Renderer::class );

// Improved head section with safer script and stylesheet inclusion.
echo $view->renderHeader( $pageTitle );

// Display header and word count.
echo $view->renderContent( $app );

$view->renderListOfArticles( $app );

// Handle form submission with sanitization.
if ( $request->isPost() ) {
	$request->handleFormSubmission( $app );
}
