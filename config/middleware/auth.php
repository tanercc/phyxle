<?php

use App\Middleware\Auth\Auth;
use Slim\Container;

$container['auth'] = function(Container $container) {
    $auth = new Auth($container);
    return $auth;
};
