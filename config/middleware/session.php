<?php

use Slim\Container;
use Slim\Middleware\Session;

$container['session'] = function(Container $container) {
    $settings = $container->get('settings')['middleware']['session'];
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
    return $session;
};
