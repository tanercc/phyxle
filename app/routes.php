<?php

use App\Controller\PublicPages;

$app->get('/', PublicPages::class . ':home');
