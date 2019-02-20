<?php

namespace App\Controller\Admin;

use App\Controller\Common\Base;
use App\Model\Admin\Account;
use Slim\Http\Request;
use Slim\Http\Response;

class Accounts extends Base
{
    /**
     * Do login functions
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

        // Check if input validation is failed
        $validation = $this->validator($request, [
            'email' => 'required|email|max:191',
            'password' => 'required|min:6|max:32'
        ]);

        if($validation !== null) {
            $this->data['error'] = reset($validation);
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Get input values
        $email = htmlspecialchars(trim($request->getParam('email')));
        $password = htmlspecialchars(trim($request->getParam('password'))) . $this->container->get('settings')['app']['key'];

        // Check if email or password is invalid
        $checkPassword = Account::where('email', $email)->value('password');

        if(!password_verify($password, $checkPassword)) {
            $this->data['error'] = "Email or Password is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Set authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_auth_token";
        $cookieValue = Account::where('email', $email)->value('unique_id');
        $cookieExpires = strtotime('1 day');
        $cookiePath = "/";

        setcookie($cookieName, $cookieValue, $cookieExpires, $cookiePath);

        // Update database
        $loggedCount = Account::where('email', $email)->value('logged_count') + 1;

        Account::where('email', $email)->update([
            'reset_key' => null,
            'logged_count' => $loggedCount,
            'last_logged_at' => $this->time::now()
        ]);

        // Return response
        return $response->withRedirect('/admin', 301);
    }

    /**
     * Do register functions
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

        // Check if input validation is failed
        $validation = $this->validator($request, [
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

        // Get input values
        $username = htmlspecialchars(trim($request->getParam('username')));
        $email = htmlspecialchars(trim($request->getParam('email')));
        $password = htmlspecialchars(trim($request->getParam('password'))) . $this->container->get('settings')['app']['key'];
        $appKey = htmlspecialchars(trim($request->getParam('app-key')));

        // Check if email is already in use
        $checkEmail = Account::where('email', $email)->first();

        if($checkEmail !== null) {
            $this->data['error'] = "There is an Already Account Using That Email";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Check if app key is invalid
        $checkAppKey = $this->container->get('settings')['app']['key'];

        if($checkAppKey !== $appKey) {
            $this->data['error'] = "Your App Key is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        $hashMethod = $this->container->get('settings')['app']['hash'];

        if($hashMethod === 'bcrypt') {
            Account::insert([
                'unique_id' => md5(bin2hex(random_bytes(16)) . $this->container->get('settings')['app']['key']),
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'created_at' => $this->time::now(),
                'updated_at' => $this->time::now()
            ]);
        }

        if($hashMethod === 'argon2i') {
            Account::insert([
                'unique_id' => md5(bin2hex(random_bytes(16)) . $this->container->get('settings')['app']['key']),
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_ARGON2I, [
                    'memory_cost' => 2048,
                    'time_cost' => 4,
                    'threads' => 2
                ]),
                'created_at' => $this->time::now(),
                'updated_at' => $this->time::now()
            ]);
        }

        if($hashMethod === 'argon2id') {
            Account::insert([
                'unique_id' => md5(bin2hex(random_bytes(16)) . $this->container->get('settings')['app']['key']),
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
        }

        // Return response
        return $response->withRedirect('/admin/account/login', 301);
    }

    /**
     * Do logout functions
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

        // Remove authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_auth_token";
        $cookieValue = "logout";
        $cookieExpires = strtotime('now') - 1;
        $cookiePath = "/";

        setcookie($cookieName, $cookieValue, $cookieExpires, $cookiePath);

        // Remove authentication session
        $sessionName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_auth";

        unset($_SESSION[$sessionName]);

        // Return response
        return $response->withRedirect('/', 301);
    }

    /**
     * Send password reset mail
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
        if($this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validation is failed
        $validation = $this->validator($request, [
            'email' => 'required|email|max:191'
        ]);

        if($validation !== null) {
            $this->data['error'] = reset($validation);
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Get input values
        $email = htmlspecialchars(trim($request->getParam('email')));

        // Check if email is invalid
        $checkEmail = Account::where('email', $email)->first();

        if($checkEmail === null) {
            $this->data['error'] = "There's No Account Using That Email";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Send password reset email
        $appName = $this->container->get('settings')['app']['name'];
        $appEmail = $this->container->get('settings')['app']['email'];
        $resetKey = bin2hex(random_bytes(3));

        $this->mail([
            'subject' => ucfirst($appName) . ' - Reset Password',
            'from' => $appEmail,
            'to' => $email,
            'body' => '<p>Someone has requested to reset your password. If this was a mistake, ignore this email.</p>' .
            '<p>Password reset key is ' . $resetKey . ' .</p>'
        ]);

        // Update database
        Account::where('email', $email)->update([
            'reset_key' => $resetKey
        ]);

        // Return response
        return $response->withRedirect('/admin/account/reset-password', 301);
    }

    /**
     * Reset forgotten password
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
        if($this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validation is failed
        $validation = $this->validator($request, [
            'reset-key' => 'required|min:6|max:6',
            'email' => 'required|email|max:191',
            'new-password' => 'required|min:6|max:32',
            'new-password-confirm' => 'required|min:6|max:32|same:new-password'
        ]);

        if($validation !== null) {
            $this->data['error'] = reset($validation);
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Get input values
        $resetKey = htmlspecialchars(trim($request->getParam('reset-key')));
        $email = htmlspecialchars(trim($request->getParam('email')));
        $newPassword = htmlspecialchars(trim($request->getParam('new-password'))) . $this->container->get('settings')['app']['key'];

        // Check if reset key is invalid
        $checkResetKey = Account::where('email', $email)->value('reset_key');

        if($resetKey !== $checkResetKey) {
            Account::where('email', $email)->update([
                'reset_key' => null
            ]);

            $this->data['error'] = "Your Reset Key is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Reset password
        $hashMethod = $this->container->get('settings')['app']['hash'];

        if($hashMethod === 'bcrypt') {
            Account::where('email', $email)->update([
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);
        }

        if($hashMethod === 'argon2i') {
            Account::where('email', $email)->update([
                'password' => password_hash($newPassword, PASSWORD_ARGON2I, [
                    'memory_cost' => 2048,
                    'time_cost' => 4,
                    'threads' => 2
                ])
            ]);
        }

        if($hashMethod === 'argon2id') {
            Account::where('email', $email)->update([
                'password' => password_hash($newPassword, PASSWORD_ARGON2ID, [
                    'memory_cost' => 2048,
                    'time_cost' => 4,
                    'threads' => 2
                ])
            ]);
        }

        // Update database
        Account::where('email', $email)->update([
            'reset_key' => null
        ]);

        // Return response
        return $response->withRedirect('/admin/account/login', 301);
    }

    /**
     * Update account details
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function updateDetails(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validation is failed
        $validation = $this->validator($request, [
            'username' => 'required|max:16',
            'email' => 'required|email|max:191',
            'current-password' => 'required|min:6|max:32'
        ]);

        if($validation !== null) {
            $this->data['error'] = reset($validation);
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Get input values
        $username = htmlspecialchars(trim($request->getParam('username')));
        $email = htmlspecialchars(trim($request->getParam('email')));
        $currentPassword = htmlspecialchars(trim($request->getParam('current-password'))) . $this->container->get('settings')['app']['key'];

        // Check if email is already in use
        $id = $this->authGet('id');
        $checkId = Account::where('email', $email)->value('id');

        if($checkId !== null && $id !== $checkId) {
            $this->data['error'] = "That Email is Already Taken";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Check if current password is invalid
        $checkCurrentPassword = Account::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data['error'] = "Current Password is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        Account::where('id', $id)->update([
            'username' => $username,
            'email' => $email
        ]);

        // Return response
        return $response->withRedirect('/admin/account', 301);
    }

    /**
     * Change account password
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function changePassword(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validation is failed
        $validation = $this->validator($request, [
            'current-password' => 'required|min:6|max:32',
            'new-password' => 'required|min:6|max:32',
            'new-password-confirm' => 'required|min:6|max:32|same:new-password'
        ]);

        if($validation !== null) {
            $this->data['error'] = reset($validation);
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Get input values
        $currentPassword = htmlspecialchars(trim($request->getParam('current-password'))) . $this->container->get('settings')['app']['key'];
        $newPassword = htmlspecialchars(trim($request->getParam('new-password'))) . $this->container->get('settings')['app']['key'];

        // Check if current password is invalid
        $id = $this->authGet('id');
        $checkCurrentPassword = Account::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data['error'] = "Current Password is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        $hashMethod = $this->container->get('settings')['app']['hash'];

        if($hashMethod === 'bcrypt') {
            Account::where('id', $id)->update([
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);
        }

        if($hashMethod === 'argon2i') {
            Account::where('id', $id)->update([
                'password' => password_hash($newPassword, PASSWORD_ARGON2I, [
                    'memory_cost' => 2048,
                    'time_cost' => 4,
                    'threads' => 2
                ])
            ]);
        }

        if($hashMethod === 'argon2id') {
            Account::where('id', $id)->update([
                'password' => password_hash($newPassword, PASSWORD_ARGON2ID, [
                    'memory_cost' => 2048,
                    'time_cost' => 4,
                    'threads' => 2
                ])
            ]);
        }

        // Return response
        return $response->withRedirect('/admin/account', 301);
    }

    /**
     * Delete your account
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function delete(Request $request, Response $response, array $data)
    {
        // Check if not authenticated
        if(!$this->authCheck) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validation is failed
        $validation = $this->validator($request, [
            'current-password' => 'required|min:6|max:32'
        ]);

        if($validation !== null) {
            $this->data['error'] = reset($validation);
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Get input values
        $currentPassword = htmlspecialchars(trim($request->getParam('current-password'))) . $this->container->get('settings')['app']['key'];

        // Check if current password is invalid
        $id = $this->authGet('id');
        $checkCurrentPassword = Account::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data['error'] = "Current Password is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        Account::where('id', $id)->delete();

        // Remove authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_auth_token";
        $cookieValue = "delete";
        $cookieExpires = strtotime('now') - 1;
        $cookiePath = "/";

        setcookie($cookieName, $cookieValue, $cookieExpires, $cookiePath);

        // Remove authentication session
        $sessionName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_auth";

        unset($_SESSION[$sessionName]);

        // Return response
        return $response->withRedirect('/', 301);
    }
}
