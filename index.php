<?php

use Slim\App;
use Slim\Container;
use Symfony\Component\Dotenv\Dotenv;

// Require Composer's autoload file
require_once __DIR__ . "/vendor/autoload.php";

// Load dotenv
$dotenv = new Dotenv;

$dotenv->load(__DIR__ . '/.env');

// Require app settings
require_once __DIR__ . "/config/app.php";

// Create dependency container
$container = new Container($settings);

// Load middleware to containers. Add new middleware bottom of this list.
require_once __DIR__ . "/config/middleware/session.php";
require_once __DIR__ . "/config/middleware/csrf.php";
require_once __DIR__ . "/config/middleware/auth.php";

// Load packages to containers. Add new packages bottom of this list.
require_once __DIR__ . "/config/view.php";
require_once __DIR__ . "/config/filesystem.php";
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/mail.php";
require_once __DIR__ . "/config/errors.php";
require_once __DIR__ . "/config/validator.php";
require_once __DIR__ . "/config/time.php";
require_once __DIR__ . "/config/image.php";

// Create Slim app
$app = new App($container);

// Load middleware
require_once __DIR__ . "/app/middleware.php";

// Load routes
require_once __DIR__ . "/app/routes.php";

// Run app
$app->run();
