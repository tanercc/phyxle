<?php

use Slim\Container;
use Slim\Csrf\Guard;
use Slim\Http\Request;
use Slim\Http\Response;

$container['csrf'] = function(Container $container) {
    $settings = $container->get('settings')['middleware']['csrf'];
    $csrf = new Guard($settings['prefix'], $settings['storage'], $settings['failureCallable'], $settings['storageLimit'], $settings['strength'], $settings['persistentTokenMode']);

    $csrf->setFailureCallable(function(Request $request, Response $response, callable $next) use($container) {
        $view = $container->get('view');
        return $response->withStatus(400)->withHeader('Content-Type', 'text/html')->write($view->render('common/errors/400.twig'));
    });

    return $csrf;
};
