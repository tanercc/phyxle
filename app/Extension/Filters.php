<?php

namespace App\Extension;

use Slim\Container;
use Twig_Error_Runtime;
use Twig_Extension;
use Twig_Filter;

class Filters extends Twig_Extension
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [
            new Twig_Filter('asset', [$this, 'asset']),
            new Twig_Filter('media', [$this, 'media']),
            new Twig_Filter('link', [$this, 'link'])
        ];
    }

    public function asset(string $file)
    {
        $filesystem = $this->container->get('filesystem');
        $url = $this->container->get('settings')['app']['url'];
        if($filesystem->exists(__DIR__ . '/../../resources/assets/' . $file)) {
            return $url . "/resources/assets/" . $file;
        } else {
            throw new Twig_Error_Runtime('Unable to find "' . $file . '".');
        }
    }

    public function media(string $file)
    {
        $filesystem = $this->container->get('filesystem');
        $url = $this->container->get('settings')['app']['url'];
        if($filesystem->exists(__DIR__ . '/../../resources/media/' . $file)) {
            return $url . "/resources/media/" . $file;
        } else {
            throw new Twig_Error_Runtime('Unable to find "' . $file . '".');
        }
    }

    public function link(string $path)
    {
        $url = $this->container->get('settings')['app']['url'];
        return $url . "/" . $path;
    }
}
