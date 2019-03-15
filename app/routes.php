<?php

use App\Controller\Admin\AdminAccounts;
use App\Controller\Admin\AdminMedia;
use App\Controller\Admin\AdminPages;
use App\Controller\PublicAccounts;
use App\Controller\PublicPages;

// Admin routes
$app->get('/admin', AdminPages::class . ':home');
$app->get('/admin/account', AdminPages::class . ':account');
$app->get('/admin/account/login', AdminAccounts::class . ':login');
$app->post('/admin/account/login', AdminAccounts::class . ':login');
$app->get('/admin/account/register', AdminAccounts::class . ':register');
$app->post('/admin/account/register', AdminAccounts::class . ':register');
$app->post('/admin/account/activate', AdminAccounts::class . ':activate');
$app->post('/admin/account/deactivate', AdminAccounts::class . ':deactivate');
$app->get('/admin/account/logout', AdminAccounts::class . ':logout');
$app->post('/admin/account/logout', AdminAccounts::class . ':logout');
$app->get('/admin/account/forgot-password', AdminAccounts::class . ':forgotPassword');
$app->post('/admin/account/forgot-password', AdminAccounts::class . ':forgotPassword');
$app->get('/admin/account/reset-password', AdminAccounts::class . ':resetPassword');
$app->post('/admin/account/reset-password', AdminAccounts::class . ':resetPassword');
$app->post('/admin/account/update-details', AdminAccounts::class . ':updateDetails');
$app->post('/admin/account/change-password', AdminAccounts::class . ':changePassword');
$app->post('/admin/account/delete', AdminAccounts::class . ':delete');
$app->get('/admin/media', AdminPages::class . ':media');
$app->post('/admin/media/upload', AdminMedia::class . ':upload');
$app->post('/admin/media/rename', AdminMedia::class . ':rename');
$app->post('/admin/media/delete', AdminMedia::class . ':delete');

// Public routes
$app->get('/', PublicPages::class . ':home');
$app->get('/account', PublicPages::class . ':account');
$app->get('/account/login', PublicAccounts::class . ':login');
$app->post('/account/login', PublicAccounts::class . ':login');
$app->get('/account/register', PublicAccounts::class . ':register');
$app->post('/account/register', PublicAccounts::class . ':register');
$app->get('/account/activate', PublicAccounts::class . ':activate');
$app->get('/account/logout', PublicAccounts::class . ':logout');
$app->post('/account/logout', PublicAccounts::class . ':logout');
$app->get('/account/forgot-password', PublicAccounts::class . ':forgotPassword');
$app->post('/account/forgot-password', PublicAccounts::class . ':forgotPassword');
$app->get('/account/reset-password', PublicAccounts::class . ':resetPassword');
$app->post('/account/reset-password', PublicAccounts::class . ':resetPassword');
$app->post('/account/update-details', PublicAccounts::class . ':updateDetails');
$app->post('/account/change-password', PublicAccounts::class . ':changePassword');
$app->post('/account/delete', PublicAccounts::class . ':delete');
