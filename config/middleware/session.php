<?php

use Slim\Container;
use Slim\Middleware\Session;

// Session container
$container['session'] = function(Container $container) {
    // Get session middleware settings
    $settings = $container->get('settings')['middleware']['session'];

    // Create session middleware object
    $session = new Session([
        'lifetime' => $settings['lifetime'],
        'path' => $settings['path'],
        'domain' => $settings['domain'],
        'secure' => $settings['secure'],
        'httpOnly' => $settings['httpOnly'],
        'name' => $settings['name'],
        'autorefresh' => $settings['autoRefresh'],
        'handler' => $settings['handler']
    ]);

    // Return session middleware object
    return $session;
};
