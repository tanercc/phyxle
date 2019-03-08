<?php

use App\Controller\Admin\AdminAccounts;
use App\Controller\Admin\AdminPages;
use App\Controller\Admin\AdminMedia;
use App\Controller\PublicPages;

// Public routes
$app->get('/', PublicPages::class . ':home');

// Admin routes
$app->get('/admin', AdminPages::class . ':home');
$app->get('/admin/account', AdminPages::class . ':account');
$app->get('/admin/account/login', AdminPages::class . ':login');
$app->post('/admin/account/login', AdminAccounts::class . ':login');
$app->get('/admin/account/register', AdminPages::class . ':register');
$app->post('/admin/account/register', AdminAccounts::class . ':register');
$app->get('/admin/account/logout', AdminPages::class . ':logout');
$app->post('/admin/account/logout', AdminAccounts::class . ':logout');
$app->get('/admin/account/forgot-password', AdminPages::class . ':forgotPassword');
$app->post('/admin/account/forgot-password', AdminAccounts::class . ':forgotPassword');
$app->get('/admin/account/reset-password', AdminPages::class . ':resetPassword');
$app->post('/admin/account/reset-password', AdminAccounts::class . ':resetPassword');
$app->post('/admin/account/update-details', AdminAccounts::class . ':updateDetails');
$app->post('/admin/account/change-password', AdminAccounts::class . ':changePassword');
$app->post('/admin/account/delete', AdminAccounts::class . ':delete');
$app->get('/admin/media', AdminPages::class . ':media');
$app->post('/admin/media/upload', AdminMedia::class . ':upload');
$app->post('/admin/media/rename', AdminMedia::class . ':rename');
$app->post('/admin/media/delete', AdminMedia::class . ':delete');
