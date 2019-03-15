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
            return $response->withRedirect('/admin/account/login');
        }

        // Set Twig data
        $this->data['accounts'] = AdminAccount::all();

        // Return homepage
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
        $this->data = [
            'accounts' => [
                'all' => AdminAccount::all(),
                'filtered' => AdminAccount::where('id', '!=', $this->admin('id'))->where('id', '!=', 1)->orderBy('logged_count', 'desc')->get()
            ]
        ];

        // Return account page
        return $this->view($response, 'admin/account.twig');
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

        // Return media page
        return $this->view($response, 'admin/media.twig');
    }
}
