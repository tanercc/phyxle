<?php

namespace App\Middleware\Admin;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Auth
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $cookie = $request->getCookieParam(strtolower($this->container->get('settings')['app']['name']) . '_auth_token');
        if($cookie !== null) {
            $token = $this->container->get('database')->table('users')->where('unique_id', $cookie)->value('unique_id');
            if($token !== null) {
                $username = $this->container->get('database')->table('users')->where('unique_id', $cookie)->value('username');
                $email = $this->container->get('database')->table('users')->where('unique_id', $cookie)->value('email');
                $lastLogin = $this->container->get('database')->table('users')->where('unique_id', $cookie)->value('last_logged_at');
                $loginCount = $this->container->get('database')->table('users')->where('unique_id', $cookie)->value('logged_count');
                $_SESSION[strtolower($this->container->get('settings')['app']['name']) . '_auth'] = [
                    'username' => $username,
                    'email' => $email,
                    'lastLogin' => $lastLogin,
                    'loginCount' => $loginCount
                ];
                return $next($request, $response);
            } else {
                setcookie(strtolower($this->container->get('settings')['app']['name']) . '_auth_token', 'invalid', time() - 1);
                unset($_SESSION[strtolower($this->container->get('settings')['app']['name']) . '_auth_token']);
                return $next($request, $response);
            }
        } else {
            unset($_SESSION[strtolower($this->container->get('settings')['app']['name']) . '_auth_token']);
            return $next($request, $response);
        }
    }
}
