<?php

namespace App\Extension;

use Slim\Container;
use Twig_Extension;
use Twig_Function;

class Functions extends Twig_Extension
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new Twig_Function('getenv', [$this, 'getenv'])
        ];
    }

    public function getenv(string $key)
    {
        return getenv($key);
    }
}
