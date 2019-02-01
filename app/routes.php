<?php

use App\Controller\Admin\Account;
use App\Controller\Admin\AdminPages;
use App\Controller\PublicPages;

$app->get('/', PublicPages::class . ':home');
$app->get('/admin/account/register', AdminPages::class . ':register');
$app->post('/admin/account/register', Account::class . ':register');
