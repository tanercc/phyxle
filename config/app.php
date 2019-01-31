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
        ]
    ]
];
