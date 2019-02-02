<?php

use App\Controller\Admin\Account;
use App\Controller\Admin\AdminPages;
use App\Controller\PublicPages;

$app->get('/', PublicPages::class . ':home');
$app->get('/admin', AdminPages::class . ':home');
$app->get('/admin/account/login', AdminPages::class . ':login');
$app->post('/admin/account/login', Account::class . ':login');
$app->get('/admin/account/register', AdminPages::class . ':register');
$app->post('/admin/account/register', Account::class . ':register');
$app->get('/admin/account/logout', AdminPages::class . ':logout');
$app->post('/admin/account/logout', Account::class . ':logout');
