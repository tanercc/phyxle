<?php

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use Slim\Container as SlimContainer;

// Database container
$container['database'] = function(SlimContainer $container) {
    // Database settings
    $settings = $container->get('settings')['database'];

    // Create database object
    $database = new Manager;

    // Configure database
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

    // Configure Eloquent ORM
    $database->setEventDispatcher(new Dispatcher(new IlluminateContainer));
    $database->setAsGlobal();
    $database->bootEloquent();

    // Return database object
    return $database;
};
