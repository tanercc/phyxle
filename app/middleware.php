<?php

// Load app middleware. Place new middleware top of list.
$app->add($container->get('adminAuth'));
$app->add($container->get('csrf'));
$app->add($container->get('session'));
