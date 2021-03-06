<?php

use Slim\App;
use Slim\Container;
use Symfony\Component\Dotenv\Dotenv;

// Require Composer's autoload file
require_once __DIR__ . "/vendor/autoload.php";

// Load dotenv
$dotenv = new Dotenv;

$dotenv->load(__DIR__ . '/.env');

// Set app timezone
date_default_timezone_set(getenv('APP_TIMEZONE'));

// Require app settings
require_once __DIR__ . "/config/app.php";

// Create dependency container
$container = new Container($settings);

// Load middleware containers. Add new middleware bottom of this list.
require_once __DIR__ . "/config/middleware/session.php";
require_once __DIR__ . "/config/middleware/csrf.php";
require_once __DIR__ . "/config/middleware/admin-auth.php";
require_once __DIR__ . "/config/middleware/public-auth.php";

// Load packages containers. Add new packages bottom of this list.
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
