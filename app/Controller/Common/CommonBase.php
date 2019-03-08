<?php

namespace App\Controller\Common;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class CommonBase
{
    // Get contained packages from containers
    protected $container;

    // Pass data to Twig templates
    protected $data = [];

    // Check authenticated as admin or not
    protected $admin;

    // Pass Carbon package to child controllers
    protected $time;

    // Pass Filesystem package to child controllers
    protected $filesystem;

    /**
     * Base controller constructor
     *
     * @param Container $container PSR-11 container object
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        // Get dependency container
        $this->container = $container;

        // Get if authenticated as admin or not
        $this->admin = (isset($_SESSION['admin']) ? true : false);

        // Get Carbon object from container
        $this->time = $container->get('time');

        // Get Filesystem object from container
        $this->filesystem = $container->get('filesystem');

        // Set Eloquent ORM to run on every request
        $container->get('database');
    }

    /**
     * Configure Twig to use in child controllers
     *
     * @param Response $response PSR-7 response object
     * @param string   $template Twig template name
     *
     * @return Response
     */
    protected function view(Response $response, string $template)
    {
        // Get Twig object from container
        $view = $this->container->get('view');

        // Return response with Twig object
        return $response->withHeader('Content-Type', 'text/html')->write($view->render($template, $this->data));
    }

    /**
     * Configure Swift Mailer to use in child controllers
     *
     * @param array  $data Mail subject, from, to and body data
     * @param string $type Mail type
     *
     * @return int
     */
    protected function mail(array $data, string $type = 'text/html')
    {
        // Create mail template
        $view = $this->container->get('view');
        $template = $view->render('common/templates/mail.twig', $data);

        // Create mail
        $message = $this->container->get('message');

        $message->setSubject($data['subject']);
        $message->setFrom($data['from']);
        $message->setTo($data['to']);
        $message->setBody($template, $type);

        // Get Swift Mailer object from container
        $mail = $this->container->get('mail');

        // Send mail
        return $mail->send($message);
    }

    /**
     * Configure input validator to use in child controllers
     *
     * @param Request $request PSR-7 request object
     * @param array   $input   Form input data
     *
     * @return array|void
     */
    protected function validator(Request $request, array $input)
    {
        // Get Validator object from container
        $validator = $this->container->get('validator');

        // Validate input fields
        $validation = $validator->validate($request->getParams(), $input);

        // Check if invalid input fields
        if($validation->fails()) {
            return $validation->errors()->firstOfAll();
        }
    }

    /**
     * Configure image manipulator to use in child controllers
     *
     * @param string $name Absolute path to image
     *
     * @return ImageManager
     */
    protected function image(string $name)
    {
        // Get Image object from container
        $image = $this->container->get('image');

        // Return image
        return $image->make($name);
    }

    /**
     * Get authenticated admin details
     *
     * @param string $key Auth session variable
     *
     * @return string|void
     */
    protected function admin(string $key)
    {
        // Check if authenticated
        if($this->admin) {
            return $_SESSION['admin'][$key];
        }
    }
}
