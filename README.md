# Phyxle
Rapid web development environment, based on [Slim](https://www.slimframework.com) framework

## Install
You can install Phyxle using [Composer](https://getcomposer.org) or clone repository using [Git](https://git-scm.com). If you cloned repository, make sure to install Composer packages using `composer install` command.
```
$ composer create-project enindu/phyxle
```
```
$ git clone https://github.com/enindu/phyxle.git
```

## Configurations
Basic configurations can be found at `.env` file and all configurations can be found at `config/app.php` file. After installation there are few things you must do manually.
- **Important:** Add `APP_KEY` in `.env` file that has 16 characters. Make sure it has characters like `!@#$&*?_1aA` and don't share it with anyone. You'll need app key to register backend users.
- Import `phyxle.sql` using [phpMyAdmin](https://www.phpmyadmin.net) or create `users` and `media` tables manually. After creating database tables make sure to delete `phyxle.sql` file.
```
CREATE TABLE `users` (
    `id` int(11) NOT NULL,
    `unique_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `username` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `logged_count` int(11) NOT NULL DEFAULT 0,
    `last_logged_at` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `updated_at` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `unique_id` (`unique_id`),
    ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
```
```
CREATE TABLE `media` (
    `id` int(11) NOT NULL,
    `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `width` int(11) NOT NULL,
    `height` int(11) NOT NULL,
    `size` int(11) NOT NULL,
    `created_at` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `updated_at` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `unique_id` (`unique_id`),
    ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `media`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
```
- Make sure to update `APP_URL` in `.env` file with your app URL
- Update `APP_MEDIA` in `.env` file with your absolute path to `resources/media` directory
- Make sure to set `APP_ERRORS` true in `.env` file in production mode

## Controllers
App controllers can be found at `app/Controller` directory. Every controller class must extends `App\Controllers\Common\Base` class.
```
namespace App\Controller;

use App\Controller\Base;
use Slim\Http\Request;
use Slim\Http\Response;

class ExmpleController extends Base
{
    public function ExampleMethod(Request $request, Response $response, array $data)
    {
        // Code goes here
        return $this->view($response, 'example.twig);
    }
}
```

## Routes
App routes can be found at `app/routes.php` file. For more information go [Slim documentation](https://www.slimframework.com/docs/v3/objects/router.html).

## Models
App models can be found at `app/Model` directory. Every model class must extends `Illuminate\Database\Eloquent\Model` class.
```
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model
{
    public $timestamps = true;
    protected $table = "example";
}
```
For more information go [Eloquent documentation](https://laravel.com/docs/5.7/eloquent).

## Views
App views can be found at `resources/views` directory. Phyxle uses [Twig](https://twig.symfony.com) as template engine. You can extend Twig by adding more filters or functions or global variables that can be found at `app/Extension` directory. For more information go [Twig documentation](https://twig.symfony.com/doc/2.x/).

## Middleware
### Create Middleware
App middleware can be found at `app/Middleware` directory.
```
namespace App\Middleware;

use Slim\Http\Request;
use Slim\Http\Response;

class ExampleMiddleware
{
    public function __invoke(Request $request, Response $response, callable $next)
    {
        // Code goes here
        return $next($request, $response);
    }
}
```
For more information go [Slim documentation](https://www.slimframework.com/docs/v3/concepts/middleware.html).

### Add Middleware
First you need add your middleware to container. For that create a new file using your middleware name in `config/middleware` directory.
```
$ cd config/middleware
$ touch example.php
```
```
use App\Middleware\ExampleMiddleware;
use Slim\Container;

$container['example'] = function(Container $container) {
    $example = new ExampleMiddleware;
    return $example;
};
```
Then load above container in `index.php` file. It is important to add require line after other middleware and before app packages.
```
require_once __DIR__ . "/config/middleware/csrf.php";
require_once __DIR__ . "/config/middleware/auth.php";
// Add your container here
require_once __DIR__ . "/config/view.php";
require_once __DIR__ . "/config/filesystem.php";
```
```
require_once __DIR__ . "/config/middleware/example.php";
```
And last add your middleware to `app/middleware.php` file. It is important to add require line top of all other middleware.
```
// Add your middleware here
$app->add($container->get('auth'));
$app->add($container->get('csrf'));
```
```
$app->add($container->get('example'));
```

## Packages
### Install Packages
You can install packages using Composer.
```
$ composer require <vendor>/<package>
```

### Add Packages
Like middleware, first you need add newly install package to container. or that create a new file using package name in `config` directory.
```
$ cd config
$ touch package.php
```
```
use Slim\Container;
use Vendor\Package;

$container['package'] = function(Container $container) {
    $package = new Package;
    return $package;
};
```
Then load above container in `index.php` file. It is important to add require line bottom of all other app packages.
```
require_once __DIR__ . "/config/time.php";
require_once __DIR__ . "/config/image.php";
// Add your container here
```
```
require_once __DIR__ . "/config/example.php";
```
After that you can use that package using `$container->get('package')` anywhere in app. (`$this->container->get('package')` in controllers)
