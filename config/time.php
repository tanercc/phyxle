<?php

use Carbon\Carbon;
use Slim\Container;

$container['time'] = function(Container $container) {
    $time = Carbon::class;
    return $time;
};
