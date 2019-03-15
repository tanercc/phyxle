<?php

// Load app middleware. Place new middleware top of list.
$app->add($container->get('public-auth'));
$app->add($container->get('admin-auth'));
$app->add($container->get('csrf'));
$app->add($container->get('session'));
