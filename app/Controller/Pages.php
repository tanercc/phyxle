<?php

namespace App\Controller;

use App\Controller\Base;
use Slim\Http\Request;
use Slim\Http\Response;

class Pages extends Base
{
    public function home(Request $request, Response $response, array $data)
    {
        return $this->view($response, 'public/home.twig');
    }
}
