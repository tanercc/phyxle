<?php

$app->add($container->get('auth'));
$app->add($container->get('csrf'));
$app->add($container->get('session'));
