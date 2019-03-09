<?php

namespace App\Controller;

use App\Controller\Common\CommonBase;
use App\Model\PublicAccount;
use Slim\Http\Request;
use Slim\Http\Response;

class PublicAccounts extends CommonBase
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
        if($this->public) {
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
        $checkPassword = PublicAccount::where('email', $email)->value('password');

        if(!password_verify($password, $checkPassword)) {
            $this->data['error'] = "Email or Password is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Check if account is not activated
        $checkActivate = PublicAccount::where('email', $email)->value('activation_token');

        if($checkActivate !== null) {
            $this->data['error'] = "You Must Activate Your Account First";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Set authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_public_auth_token";
        $cookieValue = PublicAccount::where('email', $email)->value('unique_id');
        $cookieExpires = strtotime('1 day');
        $cookiePath = "/";

        setcookie($cookieName, $cookieValue, $cookieExpires, $cookiePath);

        // Update database
        $loggedCount = PublicAccount::where('email', $email)->value('logged_count') + 1;

        PublicAccount::where('email', $email)->update([
            'reset_token' => null,
            'logged_count' => $loggedCount,
            'last_logged_at' => $this->time::now()
        ]);

        // Return response
        return $response->withRedirect('/', 301);
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
        if($this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validation is failed
        $validation = $this->validator($request, [
            'username' => 'required|max:16',
            'email' => 'required|email|max:191',
            'password' => 'required|min:6|max:32',
            'password-confirm' => 'required|min:6|max:32|same:password'
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
        $checkEmail = PublicAccount::where('email', $email)->first();

        if($checkEmail !== null) {
            $this->data['error'] = "There is an Already Account Using That Email";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        $hashMethod = $this->container->get('settings')['app']['hash'];
        $activationToken = bin2hex(random_bytes(32));

        if($hashMethod === 'bcrypt') {
            PublicAccount::insert([
                'unique_id' => bin2hex(random_bytes(16)) . $this->container->get('settings')['app']['key'],
                'activation_token' => $activationToken,
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'created_at' => $this->time::now(),
                'updated_at' => $this->time::now()
            ]);
        }

        if($hashMethod === 'argon2i') {
            PublicAccount::insert([
                'unique_id' => bin2hex(random_bytes(16)) . $this->container->get('settings')['app']['key'],
                'activation_token' => $activationToken,
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
            PublicAccount::insert([
                'unique_id' => bin2hex(random_bytes(16)) . $this->container->get('settings')['app']['key'],
                'activation_token' => $activationToken,
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

        // Send account activation email
        $appName = $this->container->get('settings')['app']['name'];
        $appUrl = $this->container->get('settings')['app']['url'];
        $appEmail = $this->container->get('settings')['app']['email'];

        $this->mail([
            'subject' => ucfirst($appName) . ' - Account Activation',
            'from' => $appEmail,
            'to' => $email,
            'body' => '<p>Hello ' . $username . '. Your account has been created. Click below link to activate account.</p>' .
            '<a href="' . $appUrl . '/account/activate?token=' . $activationToken . '" target="_blank">Activate Account</a>'
        ]);

        // Return response
        $this->data['title'] = "Check Your Email";
        $this->data['subtitle'] = "Account Activation Email Has Been Sent";

        return $this->view($response, 'common/templates/message.twig');
    }

    /**
     * Activate account
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function activate(Request $request, Response $response, array $data)
    {
        // Check if authenticated
        if($this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Get activation token
        $activationToken = $request->getQueryParam('token');

        // Check if activation token is invalid
        $checkActivationToken = PublicAccount::where('activation_token', $activationToken)->first();

        if($checkActivationToken === null) {
            $this->data['error'] = "Activation Token is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        PublicAccount::where('activation_token', $activationToken)->update([
            'activation_token' => null
        ]);

        // Return response
        $this->data['title'] = "Account Activated";
        $this->data['subtitle'] = "Now You Can Login to Your Account";

        return $this->view($response, 'common/templates/message.twig');
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
        if(!$this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Remove authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_public_auth_token";
        $cookieValue = "logout";
        $cookieExpires = strtotime('now') - 1;
        $cookiePath = "/";

        setcookie($cookieName, $cookieValue, $cookieExpires, $cookiePath);

        // Remove authentication session
        unset($_SESSION['admin']);

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
        if($this->public) {
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
        $checkEmail = PublicAccount::where('email', $email)->first();

        if($checkEmail === null) {
            $this->data['error'] = "There's No Account Using That Email";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        $resetToken = bin2hex(random_bytes(32));

        PublicAccount::where('email', $email)->update([
            'reset_token' => $resetToken
        ]);

        // Send password reset email
        $appName = $this->container->get('settings')['app']['name'];
        $appUrl = $this->container->get('settings')['app']['url'];
        $appEmail = $this->container->get('settings')['app']['email'];

        $this->mail([
            'subject' => ucfirst($appName) . ' - Reset Password',
            'from' => $appEmail,
            'to' => $email,
            'body' => '<p>Someone has requested to reset your password. If this was a mistake, ignore this email.</p>' .
            '<a href="' . $appUrl . '/account/reset-password?token=' . $resetToken . '" target="_blank">Reset Password</a>'
        ]);

        // Return response
        $this->data['title'] = "Check Your Email";
        $this->data['subtitle'] = "Password Reset Email Has Been Sent";

        return $this->view($response, 'common/templates/message.twig');
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
        if($this->public) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validation is failed
        $validation = $this->validator($request, [
            'email' => 'required|email|max:191',
            'new-password' => 'required|min:6|max:32',
            'new-password-confirm' => 'required|min:6|max:32|same:new-password'
        ]);

        if($validation !== null) {
            $this->data['error'] = reset($validation);
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Get input values
        $resetToken = htmlspecialchars(trim($request->getParam('reset-token')));
        $email = htmlspecialchars(trim($request->getParam('email')));
        $newPassword = htmlspecialchars(trim($request->getParam('new-password'))) . $this->container->get('settings')['app']['key'];

        // Check if reset token is invalid
        $checkResetToken = PublicAccount::where('email', $email)->value('reset_token');

        if($resetToken !== $checkResetToken) {
            PublicAccount::where('email', $email)->update([
                'reset_token' => null
            ]);

            $this->data['error'] = "Your Reset Token is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Reset password
        $hashMethod = $this->container->get('settings')['app']['hash'];

        if($hashMethod === 'bcrypt') {
            PublicAccount::where('email', $email)->update([
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);
        }

        if($hashMethod === 'argon2i') {
            PublicAccount::where('email', $email)->update([
                'password' => password_hash($newPassword, PASSWORD_ARGON2I, [
                    'memory_cost' => 2048,
                    'time_cost' => 4,
                    'threads' => 2
                ])
            ]);
        }

        if($hashMethod === 'argon2id') {
            PublicAccount::where('email', $email)->update([
                'password' => password_hash($newPassword, PASSWORD_ARGON2ID, [
                    'memory_cost' => 2048,
                    'time_cost' => 4,
                    'threads' => 2
                ])
            ]);
        }

        // Update database
        PublicAccount::where('email', $email)->update([
            'reset_token' => null
        ]);

        // Return response
        return $response->withRedirect('/account/login', 301);
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
        if(!$this->public) {
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
        $id = $this->public('id');
        $checkId = PublicAccount::where('email', $email)->value('id');

        if($checkId !== null && $id !== $checkId) {
            $this->data['error'] = "That Email is Already Taken";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Check if current password is invalid
        $checkCurrentPassword = PublicAccount::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data['error'] = "Current Password is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        PublicAccount::where('id', $id)->update([
            'username' => $username,
            'email' => $email
        ]);

        // Return response
        return $response->withRedirect('/account', 301);
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
        if(!$this->public) {
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
        $id = $this->public('id');
        $checkCurrentPassword = PublicAccount::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data['error'] = "Current Password is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        $hashMethod = $this->container->get('settings')['app']['hash'];

        if($hashMethod === 'bcrypt') {
            PublicAccount::where('id', $id)->update([
                'password' => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);
        }

        if($hashMethod === 'argon2i') {
            PublicAccount::where('id', $id)->update([
                'password' => password_hash($newPassword, PASSWORD_ARGON2I, [
                    'memory_cost' => 2048,
                    'time_cost' => 4,
                    'threads' => 2
                ])
            ]);
        }

        if($hashMethod === 'argon2id') {
            PublicAccount::where('id', $id)->update([
                'password' => password_hash($newPassword, PASSWORD_ARGON2ID, [
                    'memory_cost' => 2048,
                    'time_cost' => 4,
                    'threads' => 2
                ])
            ]);
        }

        // Return response
        return $response->withRedirect('/account', 301);
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
        if(!$this->public) {
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
        $id = $this->public('id');
        $checkCurrentPassword = PublicAccount::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data['error'] = "Current Password is Invalid";
            return $this->view($response->withStatus(400), 'common/templates/validation.twig');
        }

        // Update database
        PublicAccount::where('id', $id)->delete();

        // Remove authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_public_auth_token";
        $cookieValue = "delete";
        $cookieExpires = strtotime('now') - 1;
        $cookiePath = "/";

        setcookie($cookieName, $cookieValue, $cookieExpires, $cookiePath);

        // Remove authentication session
        unset($_SESSION['public']);

        // Return response
        return $response->withRedirect('/', 301);
    }
}
