<?php

namespace App\Controller\Admin;

use App\Controller\Common\Base;
use Slim\Http\Request;
use Slim\Http\Response;

class AdminPages extends Base
{
    public function register(Request $request, Response $response, array $data)
    {
        if(!$this->authCheck) {
            return $this->view($response, 'admin/register.twig');
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }
}
