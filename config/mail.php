<?php

use Slim\Container;

// Mail container
$container['mail'] = function(Container $container) {
    // Mail settings
    $settings = $container->get('settings')['mail'];

    // Create transport object
    $transport = new Swift_SmtpTransport;

    // Configure transport
    $transport->setHost($settings['host']);
    $transport->setPort($settings['port']);
    $transport->setUsername($settings['username']);
    $transport->setPassword($settings['password']);

    // Create mail object
    $mail = new Swift_Mailer($transport);

    // Return mail object
    return $mail;
};

// Message container
$container['message'] = function(Container $container) {
    // Create message object
    $message = new Swift_Message;

    // Return message object
    return $message;
};
