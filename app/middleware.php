<?php

// Load app middleware. Place new middleware top of list.
$app->add($container->get('auth'));
$app->add($container->get('csrf'));
$app->add($container->get('session'));
