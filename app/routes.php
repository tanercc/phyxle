<?php

use App\Controller\Pages;

$app->get('/', Pages::class . ':home');
