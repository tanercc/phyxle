<?php

use Slim\Container;

$container['mail'] = function(Container $container) {
    $settings = $container->get('settings')['mail'];
    $transport = new Swift_SmtpTransport;
    $transport->setHost($settings['host']);
    $transport->setPort($settings['port']);
    $transport->setUsername($settings['username']);
    $transport->setPassword($settings['password']);
    $mail = new Swift_Mailer($transport);
    return $mail;
};

$container['message'] = function(Container $container) {
    $message = new Swift_Message;
    return $message;
};
