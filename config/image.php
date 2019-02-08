<?php

use Intervention\Image\ImageManager;
use Slim\Container;

// Image container
$container['image'] = function(Container $container) {
    // Image settings
    $settings = $container->get('settings')['image'];

    // Create image object
    $image = new ImageManager([
        'driver' => $settings['driver']
    ]);

    // Return image object
    return $image;
};
