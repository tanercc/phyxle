<?php

use App\Extension\Filters;
use App\Extension\Functions;
use App\Extension\Globals;
use Slim\Container;

// View container
$container['view'] = function(Container $container) {
    // View settings
    $settings = $container->get('settings')['view'];

    // Create view object
    $view = new Twig_Environment(new Twig_Loader_Filesystem($settings['views']), [
        'debug' => $settings['debug'],
        'charset' => $settings['charset'],
        'base_template_class' => $settings['baseTemplateClass'],
        'cache' => $settings['cache'],
        'auto_reload' => $settings['autoReload'],
        'strict_variables' => $settings['strictVariables'],
        'autoescape' => $settings['autoEscape'],
        'optimizations' => $settings['optimizations']
    ]);

    // Add custom view extensions
    $view->addExtension(new Filters($container));
    $view->addExtension(new Functions($container));
    $view->addExtension(new Globals($container));

    // Return view object
    return $view;
};
