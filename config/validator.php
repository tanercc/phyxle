<?php

use Rakit\Validation\Validator;
use Slim\Container;

$container['validator'] = function(Container $container) {
    $settings = $container->get('settings')['validator'];
    $validator = new Validator;
    $validator->setMessages([
        'required' => $settings['required'],
        'min' => $settings['min'],
        'max' => $settings['max'],
        'email' => $settings['email'],
        'same' => $settings['same']
    ]);
    return $validator;
};
