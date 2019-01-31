<?php

namespace App\Extension;

use Slim\Container;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;

class Globals extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getGlobals()
    {
        return [
            'app' => [
                'name' => $this->container->get('settings')['app']['name'],
                'description' => $this->container->get('settings')['app']['description'],
                'keywords' => $this->container->get('settings')['app']['keywords'],
                'author' => $this->container->get('settings')['app']['author']
            ],
            'csrf' => [
                'name' => [
                    'key' => $this->container->get('csrf')->getTokenNameKey(),
                    'value' => $this->container->get('csrf')->getTokenName()
                ],
                'token' => [
                    'key' => $this->container->get('csrf')->getTokenValueKey(),
                    'value' => $this->container->get('csrf')->getTokenValue()
                ]
            ]
        ];
    }
}
