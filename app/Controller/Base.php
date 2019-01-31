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
}
