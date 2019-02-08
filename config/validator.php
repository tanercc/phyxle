<?php

use Rakit\Validation\Validator;
use Slim\Container;

// Validator container
$container['validator'] = function(Container $container) {
    // Validator settings
    $settings = $container->get('settings')['validator'];

    // Create validator object
    $validator = new Validator;

    // Add custom error messages to validation
    $validator->setMessages([
        'required' => $settings['required'],
        'min' => $settings['min'],
        'max' => $settings['max'],
        'email' => $settings['email'],
        'same' => $settings['same']
    ]);

    // Return validator object
    return $validator;
};
