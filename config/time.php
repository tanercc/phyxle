<?php

use Carbon\Carbon;
use Slim\Container;

// Time container
$container['time'] = function(Container $container) {
    // Get time class
    $time = Carbon::class;

    // Return time class
    return $time;
};
