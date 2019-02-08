<?php

use Slim\Container;
use Symfony\Component\Filesystem\Filesystem;

// Filesystem container
$container['filesystem'] = function(Container $container) {
    // Create filesystem object
    $filesystem = new Filesystem;

    // Return filesystem object
    return $filesystem;
};
