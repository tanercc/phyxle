<?php

namespace App\Controller\Common;

use Slim\Container;
use Slim\Http\Response;

class Base
{
    protected $container;
    protected $data;
    protected $authCheck;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->data = [];
        $this->authCheck = (isset($_SESSION[strtolower($container->get('settings')['app']['name']) . '_auth']) ? true : false);
        $container->get('database');
    }

    protected function view(Response $response, string $template)
    {
        $view = $this->container->get('view');
        return $response->withHeader('Content-Type', 'text/html')->write($view->render($template, $this->data));
    }

    protected function mail(array $data, string $type = 'text/html')
    {
        $view = $this->container->get('view');
        $template = $view->render('common/templates/mail.twig', $data);
        $message = $this->container->get('message');
        $message->setSubject($data['subject']);
        $message->setFrom($data['from']);
        $message->setTo($data['to']);
        $message->setBody($template, $type);
        $mail = $this->container->get('mail');
        return $mail->send($message);
    }

    protected function authGet(string $key)
    {
        if($this->authCheck) {
            return $_SESSION[strtolower($container->get('settings')['app']['name'])][$key];
        }
    }
}
