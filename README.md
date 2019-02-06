# Phyxle
Rapid web development environment, based on [Slim](https://www.slimframework.com) framework

## Features
- Based on [Slim](https://www.slimframework.com) framework
- Template management [Twig](https://twig.symfony.com) library
- Database management with [Eloquent ORM](https://laravel.com/docs/5.7/eloquent) library
- Mail management with [Swift Mailer](https://swiftmailer.symfony.com) library
- Date and time manipulation with [Carbon](https://carbon.nesbot.com) library
- File system management with [Filesystem](https://github.com/symfony/filesystem) library
- Input validation with [Validation](https://github.com/rakit/validation) library
- Image manipulation with [Image](http://image.intervention.io) library
- Session management with [Slim Session](https://github.com/bryanjhv/slim-session) library
- CSRF protection with [Slim CSRF](https://github.com/slimphp/Slim-Csrf) library
- Pre-configured simple user authentication system and admin panel

## Install
Install Phyxle using [Composer](https://getcomposer.org) or clone repository using [Git](https://git-scm.com). If you cloned repository, make sure to install [Composer](https://getcomposer.org) packages using `composer install` command.
```
$ composer create-project enindu/phyxle <project name>
```
```
$ git clone https://github.com/enindu/phyxle.git
```

## Configuration
Basic configurations can be found at `.env` file and all configurations can be found at `config/app.php` file. After installation there are few things you need do manually.

- Add `APP_KEY` in `.env` file that has to be 16 characters long. Make sure it has special characters like `!@#$&*?` and combination of numbers, uppercase characters and lowercase characters. Don't share it with anyone. You'll need app key to register backend users.
- Import `phyxle.sql` using [phpMyAdmin](https://www.phpmyadmin.net) or create `users` and `media` tables manually. After creating database tables make sure to delete `phyxle.sql` file.
```sql
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
```sql
CREATE TABLE `media` (
    `id` int(11) NOT NULL,
    `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `width` int(11) NOT NULL,
    `height` int(11) NOT NULL,
    `size` int(11) NOT NULL,
    `created_at` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
    `updated_at` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `media`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `media`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
```
- Make sure to update `APP_URL` in `.env` file with your app URL
- Update `APP_MEDIA` in `.env` file with your absolute path to `resources/media` directory
- Make sure to set `APP_ERRORS` true in `.env` file in production mode

## Controllers
App controllers can be found at `app/Controller` directory. Every controller class must extends `App\Controller\Common\Base` class.
```php
namespace App\Controller;

use App\Controller\Base;
use Slim\Http\Request;
use Slim\Http\Response;

class ExmpleController extends Base
{
    public function exampleMethod(Request $request, Response $response, array $data)
    {
        // Code goes here
    }
}
```

Get contained packages in controllers by using `$this->container` variable from `Base` controller.
```php
$package = $this->container->get('package');
```

Manage templates in controllers by using `$this->view` method from `Base` controller. To pass data as [Twig](https://twig.symfony.com) variable, use `$this->data` variable from `Base` controller. For more information, refer [Twig documentation](https://twig.symfony.com/doc/2.x).
```php
$this->data['variable'] = "Some data";
$this->view($response, 'example.twig');
```

Send mails in controllers by using `$this->mail` method from `Base` controller. For more information, refer [Swift Mailer documentation](https://swiftmailer.symfony.com/docs/introduction.html).
```php
$this->mail([
    'subject' => 'Mail Subject',
    'from' => ['John Doe' => 'john@example.com'],
    'to' => ['Brad Peter' => 'brad@example.com'],
    'body' => 'Mail Body'
]);
```

Manipulate time and data in controllers by using `$this->time` variable from `Base` controller. For more information, refer [Carbon documentation](https://carbon.nesbot.com/docs).
```php
$this->time
```

Manage file system in controllers by using `$this->filesystem` variable from `Base` controller. For more information, refer [Filesystem documentation](https://symfony.com/doc/current/components/filesystem.html).
```php
$this->filesystem
```

Validate form input fields in controllers by using `$this->validator` method from `Base` controller. For more information, refer [Validation documentation](https://github.com/rakit/validation/blob/master/README.md).
```php
$validation = $this->validator($request, [
    'input' => 'required|min:6|max:16'
]);
```

Manipulate images in controllers by using `$this->image` method from `Base` controller. For more information, refer [Image documentation](http://image.intervention.io).
```php
$image = $this->image('path/to/image.jpg');
```

Get current user `id`, `username`, `email`, `lastLogin` and `loginCount` by using `$this->authGet` method from `Base` controller. To check if authenticated, use `$this->authCheck` variable from `Base` controller.
```php
$this->authGet('username');
```

## Routes
App routes can be found at `app/routes.php` file. For more information, refer [Slim documentation](https://www.slimframework.com/docs).
```php
use App\Controller\ExampleController;

$app->get('/example-route', ExampleController::class . ':exampleMethod');
```

## Models
App models can be found at `app/Model` directory. Every model class must extends `Illuminate\Database\Eloquent\Model` class. For more information, refer [Eloquent ORM documentation](https://laravel.com/docs/5.7/eloquent).
```php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model
{
    public $timestamps = true;
    protected $table = "example";
}
```

## Views
App views can be found at `resources/views` directory. Phyxle uses [Twig](https://twig.symfony.com) as template engine. You can extend Twig by adding more filters or functions or global variables that can be found at `app/Extension` directory. For more information, refer [Twig documentation](https://twig.symfony.com/doc/2.x/).

Define assets by using `asset` filter.
```twig
{{ 'css/example.css'|asset }}
```

Define uploaded media from admin panel by using `media` filter.
```twig
{{ 'example.jpg'|media }}
```

Define internal page URLs by using `link` filter.
```twig
{{ 'path/to/page'|link }}
```

Get environment variables in `.env` file by using `getenv` function.
```twig
{{ getenv('VARIABLE') }}
```

Here's all other global variables available.
```twig
{{ app.name }}
{{ app.description }}
{{ app.keywords }}
{{ app.author }}
{{ auth.check }}
{{ auth.username }}
{{ auth.email }}
{{ auth.login.last }}
{{ auth.login.count }}
{{ csrf.name.key }}
{{ csrf.name.value }}
{{ csrf.token.key }}
{{ csrf.token.value }}
```

**Note:** You must use CSRF name and token fields in every forms.
```twig
<input name="{{ csrf.name.key }}" type="hidden" value="{{ csrf.name.value }}" />
<input name="{{ csrf.token.key }}" type="hidden" value="{{ csrf.token.value }}" />
```

## Middleware
App middleware can be found at `app/Middleware` directory. For more information go [Slim documentation](https://www.slimframework.com/docs).

```php
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

After creating or installing middleware, first you need add your middleware to container. For that create a new file using your middleware name in `config/middleware` directory.
```
$ cd config/middleware
$ touch example.php
```
```php
use App\Middleware\ExampleMiddleware;
use Slim\Container;

$container['example'] = function(Container $container) {
    $example = new ExampleMiddleware;
    return $example;
};
```
Then load above container in `index.php` file. It is important to add require line after other middleware and before packages.
```php
require_once __DIR__ . "/config/middleware/csrf.php";
require_once __DIR__ . "/config/middleware/auth.php";
// Add your container here
require_once __DIR__ . "/config/view.php";
require_once __DIR__ . "/config/filesystem.php";
```
```php
require_once __DIR__ . "/config/middleware/example.php";
```
After that load your middleware from `app/middleware.php` file. It is important to add require line top of all other middleware.
```php
// Add your middleware here
$app->add($container->get('auth'));
$app->add($container->get('csrf'));
```
```php
$app->add($container->get('example'));
```

## Packages
You can install packages using Composer. For more information, refer [Composer documentation](https://getcomposer.org/doc).
```
$ composer require <vendor>/<package>
```

Like with middleware, after installing package, first you need to add newly install package to container. For that create a new file using package name in `config` directory.
```
$ cd config
$ touch package.php
```
```php
use Slim\Container;
use Vendor\Package;

$container['package'] = function(Container $container) {
    $package = new Package;
    return $package;
};
```
Then load above container in `index.php` file. It is important to add require line bottom of all other packages.
```php
require_once __DIR__ . "/config/time.php";
require_once __DIR__ . "/config/image.php";
// Add your container here
```
```php
require_once __DIR__ . "/config/example.php";
```
After that you can use package by using `Slim\Container` class.
```php
$container->get('package');
$this->container->get('package');
```
