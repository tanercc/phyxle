<?php

use App\Middleware\PublicAuth;
use Slim\Container;

// Public auth container
$container['public-auth'] = function(Container $container) {
    // Create public auth middleware object
    $publicAuth = new PublicAuth($container);

    // Return public auth middleware object
    return $publicAuth;
};
