<?php

namespace App\Extension;

use Slim\Container;
use Twig_Error_Runtime;
use Twig_Extension;
use Twig_Filter;

class Filters extends Twig_Extension
{
    // Get contained packages from containers
    private $container;

    /**
     * Filters extension constructor
     *
     * @param Container $container PSR-11 container object
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        // Get dependency container
        $this->container = $container;
    }

    /**
     * Define Twig filters
     *
     * @return array
     */
    public function getFilters()
    {
        // Return filters
        return [
            // Asset filter
            new Twig_Filter('asset', [$this, 'asset']),

            // Media filter
            new Twig_Filter('media', [$this, 'media']),

            // Thumbnail filter
            new Twig_Filter('thumbnail', [$this, 'thumbnail']),

            // URL filter
            new Twig_Filter('link', [$this, 'link'])
        ];
    }

    /**
     * Asset filter to use stylesheets, scripts, images, etc. in Twig templates
     *
     * @param string $file Asset path
     *
     * @return string
     *
     * @throws Twig_Error_Runtime
     */
    public function asset(string $file)
    {
        // Get Filesystem object from container
        $filesystem = $this->container->get('filesystem');

        // Get absolute path of asset
        $asset = __DIR__ . "/../../resources/assets/" . $file;

        // Check if asset not available
        if(!$filesystem->exists($asset)) {
            throw new Twig_Error_Runtime('Unable to find "' . $file . '".');
        }

        // Return asset URL
        return $this->container->get('settings')['app']['url'] . "/resources/assets/" . $file;
    }

    /**
     * Media filter to get uploaded media in Twig templates
     *
     * @param string $file Medium name
     *
     * @return string
     *
     * @throws Twig_Error_Runtime
     */
    public function media(string $file)
    {
        // Get Filesystem object from container
        $filesystem = $this->container->get('filesystem');

        // Get absolute path of medium
        $medium = __DIR__ . "/../../resources/media/originals/" . $file;

        // Check if medium not available
        if(!$filesystem->exists($medium)) {
            throw new Twig_Error_Runtime('Unable to find "' . $file . '".');
        }

        // Return medium URL
        return $this->container->get('settings')['app']['url'] . "/resources/media/originals/" . $file;
    }

    /**
     * Thumbnail filter to get thumbnail of uploaded media in Twig templates
     *
     * @param string $file Thumbnail name
     *
     * @return string
     *
     * @throws Twig_Error_Runtime
     */
    public function thumbnail(string $file)
    {
        // Get Filesystem object from container
        $filesystem = $this->container->get('filesystem');

        // Get absolute path of thumbnail of medium
        $thumbnail = __DIR__ . "/../../resources/media/thumbnails/" . $file;

        // Check if thumbnail of medium not available
        if(!$filesystem->exists($thumbnail)) {
            throw new Twig_Error_Runtime('Unable to find "' . $file . '".');
        }

        // Return thumbnail of medium URL
        return $this->container->get('settings')['app']['url'] . "/resources/media/thumbnails/" . $file;
    }

    /**
     * Link filter to define internal URLs in Twig templates
     *
     * @param string $path URL path
     *
     * @return string
     */
    public function link(string $path)
    {
        // Return URL
        return $this->container->get('settings')['app']['url'] . $path;
    }
}
