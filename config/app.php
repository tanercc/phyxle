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
        ]
    ]
];
