<?php

use App\Middleware\Admin\Auth;
use Slim\Container;

$container['auth'] = function(Container $container) {
    $auth = new Auth($container);
    return $auth;
};
