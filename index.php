<?php

use Slim\App;
use Slim\Container;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . "/vendor/autoload.php";

$dotenv = new Dotenv;
$dotenv->load(__DIR__ . '/.env');

require_once __DIR__ . "/config/app.php";

$container = new Container($settings);

require_once __DIR__ . "/config/view.php";
require_once __DIR__ . "/config/filesystem.php";
require_once __DIR__ . "/config/database.php";

$app = new App($container);

require_once __DIR__ . "/app/routes.php";

$app->run();
