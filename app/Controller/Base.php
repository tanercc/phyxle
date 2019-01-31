<?php

namespace App\Controller;

use Slim\Container;
use Slim\Http\Response;

class Base
{
    protected $container;
    protected $data;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->data = [];
        $container->get('database');
    }

    protected function view(Response $response, string $template)
    {
        $view = $this->container->get('view');
        return $response->withHeader('Content-Type', 'text/html')->write($view->render($template, $this->data));
    }

    protected function mail(string $subject, array $from, array $to, string $body, string $type = 'text/html')
    {
        $view = $this->container->get('view');
        $template = $view->render('common/templates/mail.twig', [
            'mail' => [
                'subject' => $subject,
                'body' => $body
            ]
        ]);
        $message = $this->container->get('message');
        $message->setSubject($subject);
        $message->setFrom($from);
        $message->setTo($to);
        $message->setBody($template, $type);
        $mail = $this->container->get('mail');
        return $mail->send($message);
    }
}
