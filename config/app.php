<?php

$settings = [
    'settings' => [
        'httpVersion' => '1.1',
        'responseChunkSize' => 4096,
        'outputBuffering' => 'prepend',
        'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => (getenv('APP_ERRORS') == 'false' ? true : false),
        'addContentLengthHeader' => true,
        'routerCacheFile' => false,
        'app' => [
            'name' => getenv('APP_NAME'),
            'description' => getenv('APP_DESCRIPTION'),
            'keywords' => getenv('APP_KEYWORDS'),
            'author' => getenv('APP_AUTHOR'),
            'url' => getenv('APP_URL'),
            'key' => getenv('APP_KEY'),
            'media' => getenv('APP_MEDIA'),
            'errors' => (getenv('APP_ERRORS') == 'true' ? true : false)
        ],
        'view' => [
            'views' => __DIR__ . '/../resources/views/',
            'debug' => false,
            'charset' => 'utf-8',
            'baseTemplateClass' => 'Twig_Template',
            'cache' => __DIR__ . '/../cache/',
            'autoReload' => true,
            'strictVariables' => true,
            'autoEscape' => false,
            'optimizations' => -1
        ],
        'database' => [
            'driver' => getenv('DB_DRIVER'),
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_DATABASE'),
            'username' => getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
            'charset' => getenv('DB_CHARSET'),
            'collation' => getenv('DB_COLLATION'),
            'prefix' => getenv('DB_PREFIX')
        ],
        'mail' => [
            'host' => getenv('MAIL_HOST'),
            'port' => getenv('MAIL_PORT'),
            'username' => getenv('MAIL_USERNAME'),
            'password' => getenv('MAIL_PASSWORD')
        ],
        'validator' => [
            'required' => 'You Cannot Leave Any Empty Field',
            'min' => 'There is a Minimum Character Limit',
            'max' => 'There is a Maximum Character Limit',
            'email' => 'Email is Invalid',
            'same' => 'Password Fields are Do Not Match'
        ],
        'image' => [
            'driver' => 'gd'
        ],
        'middleware' => [
            'session' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httpOnly' => false,
                'name' => strtolower(getenv('APP_NAME')) . '_session',
                'autoRefresh' => false,
                'handler' => null
            ],
            'csrf' => [
                'prefix' => strtolower(getenv('APP_NAME')) . '_csrf',
                'storage' => null,
                'failureCallable' => null,
                'storageLimit' => 200,
                'strength' => 64,
                'persistentTokenMode' => false
            ]
        ]
    ]
];
