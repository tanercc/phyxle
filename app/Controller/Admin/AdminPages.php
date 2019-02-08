<?php

namespace App\Controller\Admin;

use App\Controller\Common\Base;
use App\Model\Admin\Account;
use App\Model\Admin\Medium;
use Slim\Http\Request;
use Slim\Http\Response;

class AdminPages extends Base
{
    /**
     * Return admin homepage
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function home(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->authCheck) {
            return $response->withRedirect('/admin/account/login', 301);
        }

        // Set Twig data
        $this->data['accounts'] = Account::all();

        // Return response
        return $this->view($response, 'admin/home.twig');
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
        if(!$this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Set Twig data
        $this->data = [
            'accounts' => Account::orderBy('id', 'asc')->get(),
            'media' => Medium::orderBy('size', 'desc')->get()
        ];

        // Return response
        return $this->view($response, 'admin/account.twig');
    }

    /**
     * Return account login page
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function login(Request $request, Response $response, array $data)
    {
        // Check if authenticated
        if($this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return response
        return $this->view($response, 'admin/account_login.twig');
    }

    /**
     * Return account register page
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function register(Request $request, Response $response, array $data)
    {
        // Check if authenticated
        if($this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return response
        return $this->view($response, 'admin/account_register.twig');
    }

    /**
     * Return account logout page
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function logout(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return response
        return $this->view($response, 'admin/account_logout.twig');
    }

    /**
     * Return media page
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function media(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Set Twig data
        $this->data['media'] = Medium::orderBy('name', 'asc')->get();

        // Return response
        return $this->view($response, 'admin/media.twig');
    }
}
