<?php

namespace App\Controller;

use App\Controller\Common\CommonBase;
use App\Model\PublicAccount;
use Slim\Http\Request;
use Slim\Http\Response;

class PublicAccounts extends CommonBase
{
    /**
     * Login functions
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

        // Check if request method is GET
        if($request->isGet()) {
            return $this->view($response, 'account_login.twig');
        }

        // Check if input validations are failed
        $validation = $this->validator($request, [
            'email' => 'required|email|max:191',
            'password' => 'required|min:6|max:32'
        ]);

        if($validation !== null) {
            $this->data = [
                'title' => reset($validation),
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get input values
        $email = htmlspecialchars(trim($request->getParam('email')));
        $password = htmlspecialchars(trim($request->getParam('password'))) . $this->container->get('settings')['app']['key'];

        // Check if email or password is invalid
        $checkPassword = PublicAccount::where('email', $email)->value('password');

        if(!password_verify($password, $checkPassword)) {
            $this->data = [
                'title' => 'Email or Password is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Check if account is not activated
        $checkActivate = PublicAccount::where('email', $email)->value('activation_token');

        if($checkActivate !== null) {
            $this->data = [
                'title' => 'Account is Not Activated Yet',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Set authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_public_auth_token";
        $cookieValue = PublicAccount::where('email', $email)->value('unique_id');
        $cookieExpires = strtotime('1 day');
        $cookiePath = "/";

        setcookie($cookieName, $cookieValue, $cookieExpires, $cookiePath);

        // Get logged count
        $loggedCount = PublicAccount::where('email', $email)->value('logged_count') + 1;

        // Update reset token, logged count and last logged at columns in public
        // accounts table
        PublicAccount::where('email', $email)->update([
            'reset_token' => null,
            'logged_count' => $loggedCount,
            'last_logged_at' => $this->time::now()
        ]);

        // Redirect to / route
        return $response->withRedirect('/');
    }

    /**
     * Register functions
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

        // Check if request method is GET
        if($request->isGet()) {
            return $this->view($response, 'account_register.twig');
        }

        // Check if input validations are failed
        $validation = $this->validator($request, [
            'username' => 'required|max:16',
            'email' => 'required|email|max:191',
            'password' => 'required|min:6|max:32',
            'password-confirm' => 'required|min:6|max:32|same:password'
        ]);

        if($validation !== null) {
            $this->data = [
                'title' => reset($validation),
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get input values
        $username = htmlspecialchars(trim($request->getParam('username')));
        $email = htmlspecialchars(trim($request->getParam('email')));
        $password = htmlspecialchars(trim($request->getParam('password'))) . $this->container->get('settings')['app']['key'];
        $appKey = htmlspecialchars(trim($request->getParam('app-key')));

        // Check if email is already in use
        $checkEmail = PublicAccount::where('email', $email)->first();

        if($checkEmail !== null) {
            $this->data = [
                'title' => 'There is an Account Already Using That Email',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get password hash method and activation token
        $hashMethod = $this->container->get('settings')['app']['hash'];
        $activationToken = bin2hex(random_bytes(32));

        // Update unique ID, activation token, username, email, password,
        // created at and updated at columns in public accounts table
        switch($hashMethod) {
            case 'bcrypt':
                PublicAccount::insert([
                    'unique_id' => md5(bin2hex(random_bytes(16)) . $this->container->get('settings')['app']['key']),
                    'activation_token' => $activationToken,
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'created_at' => $this->time::now(),
                    'updated_at' => $this->time::now()
                ]);

                break;

            case 'argon2i':
                PublicAccount::insert([
                    'unique_id' => md5(bin2hex(random_bytes(16)) . $this->container->get('settings')['app']['key']),
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

                break;

            case 'argon2id':
                PublicAccount::insert([
                    'unique_id' => md5(bin2hex(random_bytes(16)) . $this->container->get('settings')['app']['key']),
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

                break;

            default:
                $this->data = [
                    'title' => 'Password Hash Method is Not Defined',
                    'subtitle' => 'HTTP Status Code: 400'
                ];

                return $this->view($response->withStatus(400), 'common/templates/message.twig');
            }

        // Send account activation email
        $this->mail([
            'subject' => ucfirst($this->container->get('settings')['app']['name']) . ' - Account Activation',
            'from' => $this->container->get('settings')['app']['email'],
            'to' => $email,
            'body' => '<p>Hello ' . $username . '! Your account has been created. Click below link to activate account.</p>' .
            '<a href="' . $this->container->get('settings')['app']['url'] . '/account/activate?token=' . $activationToken . '" target="_blank">Activate Account</a>'
        ]);

        // Return message page
        $this->data = [
            'title' => 'Check Your Email',
            'subtitle' => 'Account Activation Mail Has Been Sent'
        ];

        return $this->view($response, 'common/templates/message.twig');
    }

    /**
     * Activate functions
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
            $this->data = [
                'title' => 'Activation Token is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Update activation token column in public accounts table
        PublicAccount::where('activation_token', $activationToken)->update([
            'activation_token' => null
        ]);

        // Return message page
        $this->data = [
            'title' => 'Account Activated',
            'subtitle' => 'Now You Can Login to Your Account'
        ];

        return $this->view($response, 'common/templates/message.twig');
    }

    /**
     * Logout functions
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

        // Check if request method is GET
        if($request->isGet()) {
            return $this->view($response, 'account_logout.twig');
        }

        // Remove authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_public_auth_token";
        $cookieValue = "logout";
        $cookieExpires = strtotime('now') - 1;
        $cookiePath = "/";

        setcookie($cookieName, $cookieValue, $cookieExpires, $cookiePath);

        // Remove authentication session
        unset($_SESSION['admin']);

        // Redirect to / route
        return $response->withRedirect('/');
    }

    /**
     * Forgot password functions
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

        // Check if request method is GET
        if($request->isGet()) {
            return $this->view($response, 'account_forgot_password.twig');
        }

        // Check if input validations are failed
        $validation = $this->validator($request, [
            'email' => 'required|email|max:191'
        ]);

        if($validation !== null) {
            $this->data = [
                'title' => reset($validation),
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get input values
        $email = htmlspecialchars(trim($request->getParam('email')));

        // Check if email is invalid
        $checkEmail = PublicAccount::where('email', $email)->first();

        if($checkEmail === null) {
            $this->data = [
                'title' => 'There is No Account Using That Email',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get reset token
        $resetToken = bin2hex(random_bytes(32));

        // Update reset token column in public accounts table
        PublicAccount::where('email', $email)->update([
            'reset_token' => $resetToken
        ]);

        // Send password reset email
        $this->mail([
            'subject' => ucfirst($this->container->get('settings')['app']['name']) . ' - Reset Password',
            'from' => $this->container->get('settings')['app']['email'],
            'to' => $email,
            'body' => '<p>Someone has requested to reset your password. If this was a mistake, ignore this email.</p>' .
            '<a href="' . $this->container->get('settings')['app']['url'] . '/account/reset-password?token=' . $resetToken . '" target="_blank">Reset Password</a>'
        ]);

        // Return message page
        $this->data = [
            'title' => 'Check Your Email',
            'subtitle' => 'Password Reset Email Has Been Sent'
        ];

        return $this->view($response, 'common/templates/message.twig');
    }

    /**
     * Reset password functions
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

        // Check if request method is GET
        if($request->isGet()) {
            $this->data['token'] = $request->getQueryParam('token');

            return $this->view($response, 'account_reset_password.twig');
        }

        // Check if input validations are failed
        $validation = $this->validator($request, [
            'email' => 'required|email|max:191',
            'new-password' => 'required|min:6|max:32',
            'new-password-confirm' => 'required|min:6|max:32|same:new-password'
        ]);

        if($validation !== null) {
            $this->data = [
                'title' => reset($validation),
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
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

            $this->data = [
                'title' => 'Reset Token is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get password hash method
        $hashMethod = $this->container->get('settings')['app']['hash'];

        // Update reset token and password columns in public accounts table
        switch($hashMethod) {
            case 'bcrypt':
                PublicAccount::where('email', $email)->update([
                    'reset_token' => null,
                    'password' => password_hash($newPassword, PASSWORD_BCRYPT)
                ]);

                break;

            case 'argon2i':
                PublicAccount::where('email', $email)->update([
                    'reset_token' => null,
                    'password' => password_hash($newPassword, PASSWORD_ARGON2I, [
                        'memory_cost' => 2048,
                        'time_cost' => 4,
                        'threads' => 2
                    ])
                ]);

                break;

            case 'argon2id':
                PublicAccount::where('email', $email)->update([
                    'reset_token' => null,
                    'password' => password_hash($newPassword, PASSWORD_ARGON2ID, [
                        'memory_cost' => 2048,
                        'time_cost' => 4,
                        'threads' => 2
                    ])
                ]);

                break;

            default:
                $this->data = [
                    'title' => 'Password Hash Method is Not Defined',
                    'subtitle' => 'HTTP Status Code: 400'
                ];

                return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Redirect to /account/login route
        return $response->withRedirect('/account/login');
    }

    /**
     * Update defails functions
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
            $this->data = [
                'title' => reset($validation),
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get input values
        $username = htmlspecialchars(trim($request->getParam('username')));
        $email = htmlspecialchars(trim($request->getParam('email')));
        $currentPassword = htmlspecialchars(trim($request->getParam('current-password'))) . $this->container->get('settings')['app']['key'];

        // Check if email is already in use
        $id = $this->public('id');
        $checkId = PublicAccount::where('email', $email)->value('id');

        if($checkId !== null && $id !== $checkId) {
            $this->data = [
                'title' => 'That Email is Already Taken',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Check if current password is invalid
        $checkCurrentPassword = PublicAccount::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data = [
                'title' => 'Current Password is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Update username and email columns in public accounts table
        PublicAccount::where('id', $id)->update([
            'username' => $username,
            'email' => $email
        ]);

        // Redirect to /account route
        return $response->withRedirect('/account');
    }

    /**
     * Change password functions
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
            $this->data = [
                'title' => reset($validation),
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get input values
        $currentPassword = htmlspecialchars(trim($request->getParam('current-password'))) . $this->container->get('settings')['app']['key'];
        $newPassword = htmlspecialchars(trim($request->getParam('new-password'))) . $this->container->get('settings')['app']['key'];

        // Check if current password is invalid
        $id = $this->public('id');
        $checkCurrentPassword = PublicAccount::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data = [
                'title' => 'Current Password is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get password hash method
        $hashMethod = $this->container->get('settings')['app']['hash'];

        // Update password column in public accounts table
        switch($hashMethod) {
            case 'bcrypt':
                PublicAccount::where('id', $id)->update([
                    'password' => password_hash($newPassword, PASSWORD_BCRYPT)
                ]);

                break;

            case 'argon2i':
                PublicAccount::where('id', $id)->update([
                    'password' => password_hash($newPassword, PASSWORD_ARGON2I, [
                        'memory_cost' => 2048,
                        'time_cost' => 4,
                        'threads' => 2
                    ])
                ]);

                break;

            case 'argon2id':
                PublicAccount::where('id', $id)->update([
                    'password' => password_hash($newPassword, PASSWORD_ARGON2ID, [
                        'memory_cost' => 2048,
                        'time_cost' => 4,
                        'threads' => 2
                    ])
                ]);

                break;

            default:
                $this->data = [
                    'title' => 'Password Hash Method is Not Defined',
                    'subtitle' => 'HTTP Status Code: 400'
                ];

                return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Redirect to /account route
        return $response->withRedirect('/account');
    }

    /**
     * Delete functions
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
            $this->data = [
                'title' => reset($validation),
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get input values
        $currentPassword = htmlspecialchars(trim($request->getParam('current-password'))) . $this->container->get('settings')['app']['key'];

        // Check if current password is invalid
        $id = $this->public('id');
        $checkCurrentPassword = PublicAccount::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data = [
                'title' => 'Current Password is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Delete current account row in public accounts table
        PublicAccount::where('id', $id)->delete();

        // Return response
        return $response->withRedirect('/');
    }
}
