<?php

namespace App\Extension;

use Slim\Container;
use Twig_Extension;
use Twig_Extension_GlobalsInterface;

class Globals extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    // Get contained packages from containers
    private $container;

    /**
     * Globals extension constructor
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
     * Define Twig global variables
     *
     * @return array
     */
    public function getGlobals()
    {
        // Return global variables
        return [
            // App array
            'app' => [
                // App name
                'name' => $this->container->get('settings')['app']['name'],

                // App version
                'version' => $this->container->get('settings')['app']['version'],

                // App description
                'description' => $this->container->get('settings')['app']['description'],

                // App keywords
                'keywords' => $this->container->get('settings')['app']['keywords'],

                // App author
                'author' => $this->container->get('settings')['app']['author'],

                // App URL
                'url' => $this->container->get('settings')['app']['url'],

                // PHP version
                'php' => phpversion()
            ],

            // Auth array
            'auth' => [
                // Admin array
                'admin' => [
                    // Check if authenticated or not
                    'check' => (isset($_SESSION['admin'])) ? true : false,

                    // Authenticated admin's username
                    'username' => (isset($_SESSION['admin'])) ? $_SESSION['admin']['username'] : null,

                    // Authenticated admin's email
                    'email' => (isset($_SESSION['admin'])) ? $_SESSION['admin']['email'] : null,

                    // Auth login array
                    'login' => [
                        // Authenticated admin's last login timestamp
                        'last' => (isset($_SESSION['admin'])) ? $_SESSION['admin']['lastLogin'] : null,

                        // Authenticated user's login count
                        'count' => (isset($_SESSION['admin'])) ? $_SESSION['admin']['loginCount'] : null
                    ]
                ],

                // Public array
                'public' => [
                    // Check if authenticated or not
                    'check' => (isset($_SESSION['public'])) ? true : false,

                    // Authenticated admin's username
                    'username' => (isset($_SESSION['public'])) ? $_SESSION['admin']['username'] : null,

                    // Authenticated admin's email
                    'email' => (isset($_SESSION['public'])) ? $_SESSION['admin']['email'] : null,

                    // Auth login array
                    'login' => [
                        // Authenticated admin's last login timestamp
                        'last' => (isset($_SESSION['public'])) ? $_SESSION['admin']['lastLogin'] : null,

                        // Authenticated user's login count
                        'count' => (isset($_SESSION['public'])) ? $_SESSION['admin']['loginCount'] : null
                    ]
                ]
            ],

            // CSRF array
            'csrf' => [
                // CSRF name array
                'name' => [
                    // CSRF name key
                    'key' => $this->container->get('csrf')->getTokenNameKey(),

                    // CSRF name value
                    'value' => $this->container->get('csrf')->getTokenName()
                ],

                // CSRF token array
                'token' => [
                    // CSRF token key
                    'key' => $this->container->get('csrf')->getTokenValueKey(),

                    // CSRF token value
                    'value' => $this->container->get('csrf')->getTokenValue()
                ]
            ]
        ];
    }
}
