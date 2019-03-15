<?php

namespace App\Controller;

use App\Controller\Common\CommonBase;
use Slim\Http\Request;
use Slim\Http\Response;

class PublicPages extends CommonBase
{
    /**
     * Return homepage
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function home(Request $request, Response $response, array $data)
    {
        // Return homepage
        return $this->view($response, 'home.twig');
    }

    /**
     * Return account page
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function account(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return account page
        return $this->view($response, 'account.twig');
    }
}
