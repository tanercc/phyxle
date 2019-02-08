<?php

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

// Check if app errors is enabled
$errors = $container->get('settings')['app']['errors'];

if($errors) {
    // Add custom error page HTTP status code 404
    $container['notFoundHandler'] = function(Container $container) {
        return function(Request $request, Response $response) use($container) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write($container->get('view')->render('common/errors/404.twig'));
        };
    };

    // Add custom error page HTTP status code 405
    $container['notAllowedHandler'] = function(Container $container) {
        return function(Request $request, Response $response, array $methods) use($container) {
            return $response->withStatus(405)->withHeader('Allow', implode(', ', $methods))->withHeader('Content-Type', 'text/html')->write($container->get('view')->render('common/errors/405.twig'));
        };
    };

    // Add custom error page HTTP status code 500
    $container['phpErrorHandler'] = function(Container $container) {
        return function(Request $request, Response $response) use($container) {
            return $response->withStatus(500)->withHeader('Content-Type', 'text/html')->write($container->get('view')->render('common/errors/500.twig'));
        };
    };
}
