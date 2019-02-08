<?php

use App\Middleware\Admin\Auth;
use Slim\Container;

// Authentication container
$container['auth'] = function(Container $container) {
    // Create authentication middleware object
    $auth = new Auth($container);

    // Return authentication middleware object
    return $auth;
};
