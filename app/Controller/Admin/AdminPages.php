<?php

namespace App\Controller\Admin;

use App\Controller\Common\CommonBase;
use App\Model\Admin\AdminAccount;
use App\Model\Admin\AdminMedium;
use Slim\Http\Request;
use Slim\Http\Response;

class AdminPages extends CommonBase
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
        // Check if not authenticated
        if(!$this->admin) {
            return $response->withRedirect('/admin/account/login', 301);
        }

        // Set Twig data
        $this->data['accounts'] = AdminAccount::all();

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
        if(!$this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Set Twig data
        $this->data['accounts'] = AdminAccount::orderBy('logged_count', 'desc')->get();

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
        if($this->admin) {
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
        if($this->admin) {
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
        if(!$this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return response
        return $this->view($response, 'admin/account_logout.twig');
    }

    /**
     * Return forgot password page
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function forgotPassword(Request $request, Response $response, array $data)
    {
        // Check if authenticated
        if($this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return response
        return $this->view($response, 'admin/account_forgot_password.twig');
    }

    /**
     * Return reset password page
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function resetPassword(Request $request, Response $response, array $data)
    {
        // Check if authenticated
        if($this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Set Twig data
        $this->data['token'] = $request->getQueryParam('token');

        // Return response
        return $this->view($response, 'admin/account_reset_password.twig');
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
        if(!$this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Set Twig data
        $this->data['media'] = AdminMedium::orderBy('name', 'asc')->get();

        // Return response
        return $this->view($response, 'admin/media.twig');
    }
}
