<?php

declare(strict_types=1);

session_start();

// DISABLE THIS WHEN IN DEV MODE
// error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

if (php_sapi_name() === 'cli-server') {
  $path = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

  if (is_file($path)) {
    return false;
  }
}

require_once __DIR__ . '/../app/bootstrap.php';

use App\Controller\Admin;
use App\Core\Router;
use App\Controller\Auth\Auth;
use App\Controller\Auth\OAuth;
use App\Controller\Test;
use App\Core\CORS;
use App\Middlewares\RequireAuth;
use App\Middlewares\AlreadyLogin;
use App\Controller\Job\Job;

$router = new Router();

$router->add('GET', '/', [Test::class, "getUUID"]);

// $router->add('GET', '/api/auth/google', [Auth::class, "googleLogin"]);
// $router->add('GET', '/api/auth/google/callback', [Auth::class, "callback"]);

$router->add('POST', '/api/auth/register', [Auth::class, 'register']);
$router->add('POST', '/api/auth/login', [Auth::class, 'login'], [[AlreadyLogin::class, 'validate']]);
$router->add('GET',  '/api/auth/me', [Auth::class, 'me'], [[RequireAuth::class, 'validate']]);
$router->add('POST', '/api/auth/logout', [Auth::class, 'logout'], [[RequireAuth::class, 'validate']]);

$router->add('GET', '/api/auth/google/callback', [OAuth::class, 'login']);
$router->add('GET', '/api/auth/google', [OAuth::class, 'register']);

$router->add('GET', '/api/admin/stats', [Admin::class, 'stats'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('GET', '/api/admin/users', [Admin::class, 'listUsers'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('GET', '/api/admin/user/{id}', [Admin::class, 'viewUser'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('PUT', '/api/admin/user/{id}/role', [Admin::class, 'updateRole'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('DELETE', '/api/admin/user/{id}', [Admin::class, 'deleteUser'], [[RequireAuth::class, 'validateAdmin']]);

$router->add('GET', '/api/jobs/test', [Job::class, 'run']);
$router->add('GET', '/api/jobs/page/{id}', [Job::class, 'index']);
$router->add('POST', '/api/jobs/create', [Job::class, 'inputJob']);
$router->add('GET', '/api/jobs/show/{id}', [Job::class, 'show']);
$router->add('GET', '/api/jobs/search/{search}/{id}', [Job::class, 'searchPaginated']);

CORS::handleCORS();

$router->resolve(
  parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
  ),
  $_SERVER['REQUEST_METHOD']
);
