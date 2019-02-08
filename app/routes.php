<?php

use App\Controller\Admin\Accounts;
use App\Controller\Admin\AdminPages;
use App\Controller\Admin\Media;
use App\Controller\PublicPages;

// Public routes
$app->get('/', PublicPages::class . ':home');

// Admin routes
$app->get('/admin', AdminPages::class . ':home');
$app->get('/admin/account', AdminPages::class . ':account');
$app->get('/admin/account/login', AdminPages::class . ':login');
$app->post('/admin/account/login', Accounts::class . ':login');
$app->get('/admin/account/register', AdminPages::class . ':register');
$app->post('/admin/account/register', Accounts::class . ':register');
$app->get('/admin/account/logout', AdminPages::class . ':logout');
$app->post('/admin/account/logout', Accounts::class . ':logout');
$app->post('/admin/account/update-details', Accounts::class . ':updateDetails');
$app->post('/admin/account/change-password', Accounts::class . ':changePassword');
$app->get('/admin/media', AdminPages::class . ':media');
$app->post('/admin/media/upload', Media::class . ':upload');
$app->post('/admin/media/rename', Media::class . ':rename');
$app->post('/admin/media/delete', Media::class . ':delete');
