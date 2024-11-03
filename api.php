<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\App;
use App\Controller\ApiController;

$app = new App();
$controller = new ApiController( $app );

// Set JSON header for the response
header( 'Content-Type: application/json' );

// Handle the incoming API request
$controller->handleRequest();
