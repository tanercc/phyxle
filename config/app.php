<?php

// Settings array
$settings = [
    // Settings container
    'settings' => [
        // Protocol version used by response
        'httpVersion' => '1.1',

        // Size of each response chunk
        'responseChunkSize' => 4096,

        // Output buffering
        'outputBuffering' => 'prepend',

        // Calculate routes before middleware
        'determineRouteBeforeAppMiddleware' => false,

        // Display Slim errors. Set false in production mode.
        'displayErrorDetails' => (getenv('APP_ERRORS') == 'false' ? true : false),

        // Add content length header to response
        'addContentLengthHeader' => true,

        // Cache file names
        'routerCacheFile' => false,

        // App array
        'app' => [
            // App name
            'name' => getenv('APP_NAME'),

            // App version
            'version' => '0.1.2',

            // App description
            'description' => getenv('APP_DESCRIPTION'),

            // App keywords
            'keywords' => getenv('APP_KEYWORDS'),

            // App author
            'author' => getenv('APP_AUTHOR'),

            // App URL
            'url' => getenv('APP_URL'),

            // App key
            'key' => getenv('APP_KEY'),

            // App absolute uploaded media path
            'media' => getenv('APP_MEDIA'),

            // Display custom errors pages instead of detailed errors
            'errors' => (getenv('APP_ERRORS') == 'true' ? true : false),

            // App hashing method
            'hash' => getenv('APP_HASH')
        ],

        // View array
        'view' => [
            // Templates path
            'views' => __DIR__ . '/../resources/views/',

            // Enable Twig debug mode
            'debug' => false,

            // Templates character set
            'charset' => 'utf-8',

            // Base template class to render
            'baseTemplateClass' => 'Twig_Template',

            // Absolute path to cache directory
            'cache' => __DIR__ . '/../cache/',

            // Enable auto reload cached templates
            'autoReload' => true,

            // Throw error if invalid template variable used
            'strictVariables' => true,

            // Auto escape characters
            'autoEscape' => false,

            // Optimize templates
            'optimizations' => -1
        ],

        // Database array
        'database' => [
            // Database driver
            'driver' => getenv('DB_DRIVER'),

            // Database host
            'host' => getenv('DB_HOST'),

            // Database name
            'database' => getenv('DB_DATABASE'),

            // Database username
            'username' => getenv('DB_USERNAME'),

            // Database password
            'password' => getenv('DB_PASSWORD'),

            // Database character set
            'charset' => getenv('DB_CHARSET'),

            // Database collation
            'collation' => getenv('DB_COLLATION'),

            // Table prefix
            'prefix' => getenv('DB_PREFIX')
        ],

        // Mail array
        'mail' => [
            // SMTP host
            'host' => getenv('MAIL_HOST'),

            // SMTP port
            'port' => getenv('MAIL_PORT'),

            // SMTP username
            'username' => getenv('MAIL_USERNAME'),

            // SMTP password
            'password' => getenv('MAIL_PASSWORD')
        ],

        // Validator array
        'validator' => [
            // Required error message
            'required' => 'You Cannot Leave Any Empty Field',

            // Minimum characters error message
            'min' => 'There is a Minimum Character Limit',

            // Maximum characters error message
            'max' => 'There is a Maximum Character Limit',

            // Email error message
            'email' => 'Email is Invalid',

            // Comparison error message
            'same' => 'Password Fields are Do Not Match'
        ],

        // Image array
        'image' => [
            // Image driver
            'driver' => 'gd'
        ],

        // Middleware array
        'middleware' => [
            // Session array
            'session' => [
                // Session lifetime
                'lifetime' => 0,

                // Session path
                'path' => '/',

                // Session domain
                'domain' => '',

                // Set session secure
                'secure' => false,

                // Set session for HTTP only
                'httpOnly' => false,

                // Session name
                'name' => strtolower(getenv('APP_NAME')) . '_session',

                // Enable session auto refresh
                'autoRefresh' => false,

                // Session handler
                'handler' => null
            ],

            // CSRF array
            'csrf' => [
                // CSRF prefix
                'prefix' => strtolower(getenv('APP_NAME')) . '_csrf',

                // CSRF storage
                'storage' => null,

                // CSRF failure callable
                'failureCallable' => null,

                // CSRF storage limit
                'storageLimit' => 200,

                // CSRF token strength
                'strength' => 64,

                // Set CSRF persistent token mode
                'persistentTokenMode' => false
            ]
        ]
    ]
];
