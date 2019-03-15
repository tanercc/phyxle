<?php

namespace App\Middleware;

use Slim\Container;
Use Slim\Http\Request;
use Slim\Http\Response;

class PublicAuth
{
    // Get contained packages from containers
    private $container;

    /**
     * Public auth middleware constructor
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
     * Public auth middleware invoker
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param callable $next     Next middleware callable
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        // Get database
        $database = $this->container->get('database');

        // Get cookie name and session name
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_public_auth_token";
        $sessionName = "public";

        // Get authentication cookie
        $cookie = $request->getCookieParam($cookieName);

        // Check if authentication cookie is not defined
        if($cookie === null) {
            // Remove authentication session
            unset($_SESSION[$sessionName]);

            // Return next middleware
            return $next($request, $response);
        }

        // Get authentication token
        $token = $database->table('public_accounts')->where('unique_id', $cookie)->value('unique_id');

        // Check if authentication token is invalid
        if($token === null) {
            // Remove authentication cookie
            $cookieValue = "invalid";
            $cookieExpires = strtotime('now') - 1;

            setcookie($cookieName, $cookieValue, $cookieExpires);

            // Remove authentication session
            unset($_SESSION[$sessionName]);

            // Return next middleware
            return $next($request, $response);
        }

        // Get authenticated user details
        $id = $database->table('public_accounts')->where('unique_id', $cookie)->value('id');
        $username = $database->table('public_accounts')->where('unique_id', $cookie)->value('username');
        $email = $database->table('public_accounts')->where('unique_id', $cookie)->value('email');
        $lastLogin = $database->table('public_accounts')->where('unique_id', $cookie)->value('last_logged_at');
        $loginCount = $database->table('public_accounts')->where('unique_id', $cookie)->value('logged_count');

        // Set authenticated user details to session
        $_SESSION[$sessionName] = [
            'id' => $id,
            'username' => $username,
            'email' => $email,
            'lastLogin' => $lastLogin,
            'loginCount' => $loginCount
        ];

        // Return next middleware
        return $next($request, $response);
    }
}
