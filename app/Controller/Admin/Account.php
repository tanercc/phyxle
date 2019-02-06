<?php

namespace App\Controller\Admin;

use App\Controller\Common\Base;
use App\Model\Admin\User;
use Slim\Http\Request;
use Slim\Http\Response;

class Account extends Base
{
    public function login(Request $request, Response $response, array $data)
    {
        if(!$this->authCheck) {
            $validation = $this->validator($request, [
                'email' => 'required|email|max:191',
                'password' => 'required|min:6|max:32'
            ]);
            if($validation === null) {
                $email = htmlspecialchars(trim($request->getParam('email')));
                $password = htmlspecialchars(trim($request->getParam('password')));
                $check = User::where('email', $email)->value('password');
                if(password_verify($password . $this->container->get('settings')['app']['key'], $check)) {
                    setcookie(strtolower($this->container->get('settings')['app']['name']) . '_auth_token', User::where('email', $email)->value('unique_id'), strtotime('1 day'), '/');
                    User::where('email', $email)->update([
                        'logged_count' => User::where('email', $email)->value('logged_count') + 1,
                        'last_logged_at' => $this->time::now()
                    ]);
                    return $response->withRedirect('/admin', 301);
                } else {
                    $this->data['error'] = "Email or Password is Invalid";
                    return $this->view($response->withStatus(400), 'common/templates/validation.twig');
                }
            } else {
                $this->data['error'] = reset($validation);
                return $this->view($response->withStatus(400), 'common/templates/validation.twig');
            }
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }

    public function register(Request $request, Response $response, array $data)
    {
        if(!$this->authCheck) {
            $validation = $this->validator($request, [
                'username' => 'required|max:16',
                'email' => 'required|email|max:191',
                'password' => 'required|min:6|max:32',
                'password-confirm' => 'required|min:6|max:32|same:password',
                'app-key' => 'required|min:16|max:16'
            ]);
            if($validation === null) {
                $email = htmlspecialchars(trim($request->getParam('email')));
                $check = User::where('email', $email)->first();
                if($check === null) {
                    $appKey = htmlspecialchars(trim($request->getParam('app-key')));
                    if($this->container->get('settings')['app']['key'] === $appKey) {
                        $username = htmlspecialchars(trim($request->getParam('username')));
                        $password = htmlspecialchars(trim($request->getParam('password')));
                        if($this->container->get('settings')['app']['hash'] === 'bcrypt') {
                            User::insert([
                                'unique_id' => bin2hex(random_bytes(16)),
                                'username' => $username,
                                'email' => $email,
                                'password' => password_hash($password . $this->container->get('settings')['app']['key'], PASSWORD_BCRYPT),
                                'created_at' => $this->time::now(),
                                'updated_at' => $this->time::now()
                            ]);
                        }
                        if($this->container->get('settings')['app']['hash'] === 'argon2i') {
                            User::insert([
                                'unique_id' => bin2hex(random_bytes(16)),
                                'username' => $username,
                                'email' => $email,
                                'password' => password_hash($password . $this->container->get('settings')['app']['key'], PASSWORD_ARGON2I, [
                                    'memory_cost' => 2048,
                                    'time_cost' => 4,
                                    'threads' => 2
                                ]),
                                'created_at' => $this->time::now(),
                                'updated_at' => $this->time::now()
                            ]);
                        }
                        if($this->container->get('settings')['app']['hash'] === 'argon2id') {
                            User::insert([
                                'unique_id' => bin2hex(random_bytes(16)),
                                'username' => $username,
                                'email' => $email,
                                'password' => password_hash($password . $this->container->get('settings')['app']['key'], PASSWORD_ARGON2ID, [
                                    'memory_cost' => 2048,
                                    'time_cost' => 4,
                                    'threads' => 2
                                ]),
                                'created_at' => $this->time::now(),
                                'updated_at' => $this->time::now()
                            ]);
                        }
                        return $response->withRedirect('/admin/account/login', 301);
                    } else {
                        $this->data['error'] = "Your App Key is Invalid";
                        return $this->view($response->withStatus(400), 'common/templates/validation.twig');
                    }
                } else {
                    $this->data['error'] = "There is an Already Account Using That Email";
                    return $this->view($response->withStatus(400), 'common/templates/validation.twig');
                }
            } else {
                $this->data['error'] = reset($validation);
                return $this->view($response->withStatus(400), 'common/templates/validation.twig');
            }
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }

    public function logout(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            setcookie(strtolower($this->container->get('settings')['app']['name']) . '_auth_token', 'logout', time() - 1, '/');
            unset($_SESSION[strtolower($this->container->get('settings')['app']['name']) . '_auth']);
            return $response->withRedirect('/', 301);
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }

    public function updateDetails(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            $validation = $this->validator($request, [
                'username' => 'required|max:16',
                'email' => 'required|email|max:191',
                'current-password' => 'required|min:6|max:32'
            ]);
            if($validation === null) {
                $email = htmlspecialchars(trim($request->getParam('email')));
                $check = User::where('email', $email)->get();
                if(count($check) < 1) {
                    $currentPassword = htmlspecialchars(trim($request->getParam('current-password')));
                    $check = User::where('id', $this->authGet('id'))->value('password');
                    if(password_verify($currentPassword . $this->container->get('settings')['app']['key'], $check)) {
                        $username = htmlspecialchars(trim($request->getParam('username')));
                        User::where('id', $this->authGet('id'))->update([
                            'username' => $username,
                            'email' => $email
                        ]);
                        return $response->withRedirect('/admin/account', 301);
                    } else {
                        $this->data['error'] = "Current Password is Invalid";
                        return $this->view($response->withStatus(400), 'common/templates/validation.twig');
                    }
                } else {
                    $this->data['error'] = "That Email is Already Taken";
                    return $this->view($response->withStatus(400), 'common/templates/validation.twig');
                }
            } else {
                $this->data['error'] = reset($validation);
                return $this->view($response->withStatus(400), 'common/templates/validation.twig');
            }
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }

    public function changePassword(Request $request, Response $response, array $data)
    {
        if($this->authCheck) {
            $validation = $this->validator($request, [
                'current-password' => 'required|min:6|max:32',
                'new-password' => 'required|min:6|max:32',
                'new-password-confirm' => 'required|min:6|max:32|same:new-password'
            ]);
            if($validation === null) {
                $currentPassword = htmlspecialchars(trim($request->getParam('current-password')));
                $check = User::where('id', $this->authGet('id'))->value('password');
                if(password_verify($currentPassword . $this->container->get('settings')['app']['key'], $check)) {
                    $newPassword = htmlspecialchars(trim($request->getParam('new-password')));
                    if($this->container->get('settings')['app']['hash'] === 'bcrypt') {
                        User::where('id', $this->authGet('id'))->update([
                            'password' => password_hash($newPassword . $this->container->get('settings')['app']['key'], PASSWORD_BCRYPT)
                        ]);
                    }
                    if($this->container->get('settings')['app']['hash'] === 'argon2i') {
                        User::where('id', $this->authGet('id'))->update([
                            'password' => password_hash($newPassword . $this->container->get('settings')['app']['key'], PASSWORD_ARGON2I, [
                                'memory_cost' => 2048,
                                'time_cost' => 4,
                                'threads' => 2
                            ])
                        ]);
                    }
                    if($this->container->get('settings')['app']['hash'] === 'argon2id') {
                        User::where('id', $this->authGet('id'))->update([
                            'password' => password_hash($newPassword . $this->container->get('settings')['app']['key'], PASSWORD_ARGON2ID, [
                                'memory_cost' => 2048,
                                'time_cost' => 4,
                                'threads' => 2
                            ])
                        ]);
                    }
                    return $response->withRedirect('/admin/account', 301);
                } else {
                    $this->data['error'] = "Current Password is Invalid";
                    return $this->view($response->withStatus(400), 'common/templates/validation.twig');
                }
            } else {
                $this->data['error'] = reset($validation);
                return $this->view($response->withStatus(400), 'common/templates/validation.twig');
            }
        } else {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }
    }
}
