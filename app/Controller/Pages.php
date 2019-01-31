<?php

namespace App\Controller;

use Slim\Http\Request;
use Slim\Http\Response;

class Pages
{
    public function home(Request $request, Response $response, array $data)
    {
        $response->write(getenv('APP_NAME'));
    }
}
