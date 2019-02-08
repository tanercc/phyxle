<?php

namespace App\Extension;

use Slim\Container;
use Twig_Extension;
use Twig_Function;

class Functions extends Twig_Extension
{
    // Get contained packages from containers
    private $container;

    /**
     * Functions extension constructor
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
     * Define Twig functions
     *
     * @return array
     */
    public function getFunctions()
    {
        // Return functions
        return [
            // Getenv function
            new Twig_Function('getenv', [$this, 'getenv'])
        ];
    }

    /**
     * Getenv function to use environment variables in Twig templates
     *
     * @param string $key Environment variable
     *
     * @return mixed
     */
    public function getenv(string $key)
    {
        // Return environment variable
        return getenv($key);
    }
}
