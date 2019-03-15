<?php

namespace App\Controller\Admin;

use App\Controller\Common\CommonBase;
use App\Model\Admin\AdminAccount;
use Slim\Http\Request;
use Slim\Http\Response;

class AdminAccounts extends CommonBase
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
        if($this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if request method is GET
        if($request->isGet()) {
            return $this->view($response, 'admin/account_login.twig');
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
        $checkPassword = AdminAccount::where('email', $email)->value('password');

        if(!password_verify($password, $checkPassword)) {
            $this->data = [
                'title' => 'Email or Password is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Check if account is not activated
        $checkActivate = AdminAccount::where('email', $email)->value('activated');

        if($checkActivate === 0) {
            $this->data = [
                'title' => 'Account is Deactivated',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Set authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_admin_auth_token";
        $cookieValue = AdminAccount::where('email', $email)->value('unique_id');
        $cookieExpires = strtotime('1 day');
        $cookiePath = "/";

        setcookie($cookieName, $cookieValue, $cookieExpires, $cookiePath);

        // Get logged count
        $loggedCount = AdminAccount::where('email', $email)->value('logged_count') + 1;

        // Update reset token, logged count and last logged at columns in admin
        // accounts table
        AdminAccount::where('email', $email)->update([
            'reset_token' => null,
            'logged_count' => $loggedCount,
            'last_logged_at' => $this->time::now()
        ]);

        // Redirect to /admin route
        return $response->withRedirect('/admin');
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
        if($this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if request method is GET
        if($request->isGet()) {
            return $this->view($response, 'admin/account_register.twig');
        }

        // Check if input validations are failed
        $validation = $this->validator($request, [
            'username' => 'required|max:16',
            'email' => 'required|email|max:191',
            'password' => 'required|min:6|max:32',
            'password-confirm' => 'required|min:6|max:32|same:password',
            'app-key' => 'required|min:16|max:16'
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
        $checkEmail = AdminAccount::where('email', $email)->first();

        if($checkEmail !== null) {
            $this->data = [
                'title' => 'There is an Account Already Using That Email',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Check if app key is invalid
        $checkAppKey = $this->container->get('settings')['app']['key'];

        if($checkAppKey !== $appKey) {
            $this->data = [
                'title' => 'App Key is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get password hash method
        $hashMethod = $this->container->get('settings')['app']['hash'];

        // Update unique ID, username, email, password, created at and updated
        // at columns in admin accounts table
        switch($hashMethod) {
            case 'bcrypt':
                AdminAccount::insert([
                    'unique_id' => md5(bin2hex(random_bytes(32)) . $this->container->get('settings')['app']['key']),
                    'username' => $username,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'created_at' => $this->time::now(),
                    'updated_at' => $this->time::now()
                ]);

                break;

            case 'argon2i':
                AdminAccount::insert([
                    'unique_id' => md5(bin2hex(random_bytes(32)) . $this->container->get('settings')['app']['key']),
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
                AdminAccount::insert([
                    'unique_id' => md5(bin2hex(random_bytes(32)) . $this->container->get('settings')['app']['key']),
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

        // Redirect to /admin/account/login route
        return $response->withRedirect('/admin/account/login');
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
        // Check if not authenticated or not authenticated as super admin
        if(!$this->admin || $this->admin('id') !== 1) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Get account ID
        $id = htmlspecialchars(trim($request->getParam('id')));

        // Update activated column in admin accounts table
        AdminAccount::where('id', $id)->update([
            'activated' => 1
        ]);

        // Redirect to /admin/account route
        return $response->withRedirect('/admin/account');
    }

    /**
     * Deactivate functions
     *
     * @param Request  $request  PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array    $data     URL parameters
     *
     * @return Response
     */
    public function deactivate(Request $request, Response $response, array $data)
    {
        // Check if not authenticated or not authenticated as super admin
        if(!$this->admin || $this->admin('id') !== 1) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Get account ID
        $id = htmlspecialchars(trim($request->getParam('id')));

        // Update activated column in admin accounts table
        AdminAccount::where('id', $id)->update([
            'activated' => 0
        ]);

        // Redirect to /admin/account route
        return $response->withRedirect('/admin/account');
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
        if(!$this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if request method is GET
        if($request->isGet()) {
            return $this->view($response, 'admin/account_logout.twig');
        }

        // Remove authentication cookie
        $cookieName = str_replace(' ', '_', strtolower($this->container->get('settings')['app']['name'])) . "_admin_auth_token";
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
        if($this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if request method is GET
        if($request->isGet()) {
            return $this->view($response, 'admin/account_forgot_password.twig');
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
        $checkEmail = AdminAccount::where('email', $email)->first();

        if($checkEmail === null) {
            $this->data = [
                'title' => 'There is No Account Using That Email',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get reset token
        $resetToken = bin2hex(random_bytes(32));

        // Update reset token column in admin accounts table
        AdminAccount::where('email', $email)->update([
            'reset_token' => $resetToken
        ]);

        // Send password reset email
        $this->mail([
            'subject' => ucfirst($this->container->get('settings')['app']['name']) . ' - Reset Password',
            'from' => $this->container->get('settings')['app']['email'],
            'to' => $email,
            'body' => '<p>Someone has requested to reset your password. If this was a mistake, ignore this email.</p>' .
            '<a href="' . $this->container->get('settings')['app']['url'] . '/admin/account/reset-password?token=' . $resetToken . '" target="_blank">Reset Password</a>'
        ]);

        // Return message page
        $this->data = [
            'title' => 'Check Your Email',
            'subtitle' => 'Password Reset Mail Has Been Sent'
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
        if($this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if request method is GET
        if($request->isGet()) {
            $this->data['token'] = $request->getQueryParam('token');

            return $this->view($response, 'admin/account_reset_password.twig');
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
        $checkResetToken = AdminAccount::where('email', $email)->value('reset_token');

        if($resetToken !== $checkResetToken) {
            AdminAccount::where('email', $email)->update([
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

        // Update reset token and password columns in admin accounts table
        switch($hashMethod) {
            case 'bcrypt':
                AdminAccount::where('email', $email)->update([
                    'reset_token' => null,
                    'password' => password_hash($newPassword, PASSWORD_BCRYPT)
                ]);

                break;

            case 'argon2i':
                AdminAccount::where('email', $email)->update([
                    'reset_token' => null,
                    'password' => password_hash($newPassword, PASSWORD_ARGON2I, [
                        'memory_cost' => 2048,
                        'time_cost' => 4,
                        'threads' => 2
                    ])
                ]);

                break;

            case 'argon2id':
                AdminAccount::where('email', $email)->update([
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

        // Redirect to /admin/account/login route
        return $response->withRedirect('/admin/account/login');
    }

    /**
     * Update details function
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
        if(!$this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validations are failed
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
        $id = $this->admin('id');
        $checkId = AdminAccount::where('email', $email)->value('id');

        if($checkId !== null && $id !== $checkId) {
            $this->data = [
                'title' => 'That Email is Already Taken',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Check if current password is invalid
        $checkCurrentPassword = AdminAccount::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data = [
                'title' => 'Current Password is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Update username and emails columns in admin accounts table
        AdminAccount::where('id', $id)->update([
            'username' => $username,
            'email' => $email
        ]);

        // Redirect to /admin/account route
        return $response->withRedirect('/admin/account');
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
        if(!$this->admin) {
            return $this->view($response->withStatus(403), 'common/errors/403.twig');
        }

        // Check if input validations are failed
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
        $id = $this->admin('id');
        $checkCurrentPassword = AdminAccount::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data = [
                'title' => 'Current Password is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Get password hash method
        $hashMethod = $this->container->get('settings')['app']['hash'];

        // Update password column in admin accounts table
        switch($hashMethod) {
            case 'bcrypt':
                AdminAccount::where('id', $id)->update([
                    'password' => password_hash($newPassword, PASSWORD_BCRYPT)
                ]);

                break;

            case 'argon2i':
                AdminAccount::where('id', $id)->update([
                    'password' => password_hash($newPassword, PASSWORD_ARGON2I, [
                        'memory_cost' => 2048,
                        'time_cost' => 4,
                        'threads' => 2
                    ])
                ]);

                break;

            case 'argon2id':
                AdminAccount::where('id', $id)->update([
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

        // Redirect to /admin/account route
        return $response->withRedirect('/admin/account');
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
        // Check if not authenticated or authenticated as super user
        if(!$this->admin || $this->admin('id') === 1) {
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
        $id = $this->admin('id');
        $checkCurrentPassword = AdminAccount::where('id', $id)->value('password');

        if(!password_verify($currentPassword, $checkCurrentPassword)) {
            $this->data = [
                'title' => 'Current Password is Invalid',
                'subtitle' => 'HTTP Status Code: 400'
            ];

            return $this->view($response->withStatus(400), 'common/templates/message.twig');
        }

        // Delete current account row in admin accounts table
        AdminAccount::where('id', $id)->delete();

        // Redirect to / route
        return $response->withRedirect('/');
    }
}
