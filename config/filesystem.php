<?php

use Slim\Container;
use Symfony\Component\Filesystem\Filesystem;

$container['filesystem'] = function(Container $container) {
    $filesystem = new Filesystem;
    return $filesystem;
};
