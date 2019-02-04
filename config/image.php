<?php

use Intervention\Image\ImageManager;
use Slim\Container;

$container['image'] = function(Container $container) {
    $settings = $container->get('settings')['image'];
    $image = new ImageManager([
        'driver' => $settings['driver']
    ]);
    return $image;
};
