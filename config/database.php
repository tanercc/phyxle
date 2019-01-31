<?php

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Slim\Container as SlimContainer;

$container['database'] = function(SlimContainer $container) {
    $settings = $container->get('settings')['database'];
    $database = new Manager;
    $database->addConnection([
        'driver' => $settings['driver'],
        'host' => $settings['host'],
        'database' => $settings['database'],
        'username' => $settings['username'],
        'password' => $settings['password'],
        'charset' => $settings['charset'],
        'collation' => $settings['collation'],
        'prefix' => $settings['prefix']
    ]);
    $database->setEventDispatcher(new Dispatcher(new IlluminateContainer));
    $database->setAsGlobal();
    $database->bootEloquent();
    return $database;
};
