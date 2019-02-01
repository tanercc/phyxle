<?php

namespace App\Controller\Admin;

use App\Controller\Common\Base;
use App\Model\Admin\User;
use Slim\Http\Request;
use Slim\Http\Response;

class Account extends Base
{
    public function register(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
        $validation = $this->validate($request, [
            'username' => 'required|max:16',
            'email' => 'required|email|max:191',
            'password' => 'required|min:6|max:32',
            'password-confirm' => 'required|min:6|max:32|same:password',
            'app-key' => 'required|min:16|max:16'
        ]);
        if($validation !== null) {
            $this->data['error'] = reset($validation);
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }
        $email = htmlspecialchars(trim($request->getParam('email')));
        $check = User::where('email', $email)->first();
        if($check !== null) {
            $this->data['error'] = "There is an already account using that email";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }
        $appKey = htmlspecialchars(trim($request->getParam('app-key')));
        if($this->container->get('settings')['app']['key'] !== $appKey) {
            $this->data['error'] = "Your app key is invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }
        $username = htmlspecialchars(trim($request->getParam('username')));
        $password = htmlspecialchars(trim($request->getParam('password')));
        $passwordConfirm = htmlspecialchars(trim($request->getParam('password-confirm')));
        User::insert([
            'unique_id' => bin2hex(random_bytes(16)),
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 2048,
                'time_cost' => 4,
                'threads' => 2
            ]),
            'created_at' => $this->time::now(),
            'updated_at' => $this->time::now()
        ]);
        return $response->withRedirect('/admin/account/login', 301);
    }
}
