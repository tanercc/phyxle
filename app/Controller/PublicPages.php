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
        // Return response
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

        // Return response
        return $this->view($response, 'account.twig');
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
        if($this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return response
        return $this->view($response, 'account_login.twig');
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
        if($this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return response
        return $this->view($response, 'account_register.twig');
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
        if(!$this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return response
        return $this->view($response, 'account_logout.twig');
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
        if($this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Return response
        return $this->view($response, 'account_forgot_password.twig');
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
        if($this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Set Twig data
        $this->data['token'] = $request->getQueryParam('token');

        // Return response
        return $this->view($response, 'account_reset_password.twig');
    }
}
