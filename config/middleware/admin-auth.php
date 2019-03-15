<?php

use App\Middleware\Admin\AdminAuth;
use Slim\Container;

// Admin auth container
$container['admin-auth'] = function(Container $container) {
    // Create admin auth middleware object
    $adminAuth = new AdminAuth($container);

    // Return admin auth middleware object
    return $adminAuth;
};
