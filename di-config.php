<?php

use App\Request;
use App\SessionManager;
use App\View\Renderer;
use DI\ContainerBuilder;
use Gregwar\Captcha\CaptchaBuilder;

return static function ( ContainerBuilder $containerBuilder ) {
	// Define dependency injections
	$containerBuilder->addDefinitions( [
		SessionManager::class => \DI\create( SessionManager::class ),
		CaptchaBuilder::class => \DI\create( CaptchaBuilder::class ),
		Request::class => \DI\autowire()->constructorParameter( 'sessionManager', \DI\get( SessionManager::class ) ),
		Renderer::class => \DI\autowire()
			->constructorParameter( 'request', \DI\get( Request::class ) )
			->constructorParameter( 'sessionManager', \DI\get( SessionManager::class ) )
			->constructorParameter( 'captchaBuilder', \DI\get( CaptchaBuilder::class ) ),
	] );
};
