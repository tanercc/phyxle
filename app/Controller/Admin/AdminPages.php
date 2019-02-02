<?php

namespace App\Controller\Admin;

use App\Controller\Common\Base;
use App\Model\Admin\User;
use Slim\Http\Request;
use Slim\Http\Response;

class AdminPages extends Base
{
    public function home(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            $this->data = [
                'appUrl' => $this->container->get('settings')['app']['url'],
                'appKey' => $this->container->get('settings')['app']['key'],
                'phpVersion' => phpVersion(),
                'usersCount' => count(User::all())
            ];
            return $this->view($response, 'admin/home.twig');
        } else {
            return $response->withRedirect('/admin/account/login', 301);
        }
    }

    public function account(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            $this->data = [
                'appUrl' => $this->container->get('settings')['app']['url'],
                'appKey' => $this->container->get('settings')['app']['key'],
                'phpVersion' => phpVersion(),
                'usersCount' => count(User::all()),
                'users' => User::all()
            ];
            return $this->view($response, 'admin/account.twig');
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }

    public function login(Request $request, Response $response, array $data)
    {
        if(!$this->authCheck) {
            return $this->view($response, 'admin/login.twig');
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }

    public function register(Request $request, Response $response, array $data)
    {
        if(!$this->authCheck) {
            return $this->view($response, 'admin/register.twig');
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }

    public function logout(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            return $this->view($response, 'admin/logout.twig');
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }
}
