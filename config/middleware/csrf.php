<?php

use Slim\Container;
use Slim\Csrf\Guard;
use Slim\Http\Request;
use Slim\Http\Response;

// CSRF container
$container['csrf'] = function(Container $container) {
    // Get CSRF middleware settings
    $settings = $container->get('settings')['middleware']['csrf'];

    // Create CSRF middleware object
    $csrf = new Guard($settings['prefix'], $settings['storage'], $settings['failureCallable'], $settings['storageLimit'], $settings['strength'], $settings['persistentTokenMode']);

    // Add custom error page for HTTP status code 400
    $csrf->setFailureCallable(function(Request $request, Response $response, callable $next) use($container) {
        // Get Twig object from container
        $view = $container->get('view');

        // Return custom error page with response
        return $response->withStatus(400)->withHeader('Content-Type', 'text/html')->write($view->render('common/errors/400.twig'));
    });

    // Return CSRF middleware object
    return $csrf;
};
