<?php

namespace App\Controller;

use App\Controller\Common\Base;
use Slim\Http\Request;
use Slim\Http\Response;

class PublicPages extends Base
{
    public function home(Request $request, Response $response, array $data)
    {
        return $this->view($response, 'home.twig');
    }
}
