<?php

declare(strict_types=1);

session_start();

// DISABLE THIS WHEN IN DEV MODE
error_reporting(~E_ALL & ~E_WARNING & ~E_NOTICE);

if (php_sapi_name() === 'cli-server') {
  $path = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

  if (is_file($path)) {
    return false;
  }
}

require_once __DIR__ . '/app/bootstrap.php';

use App\Core\DB;
use App\Controller\Admin;
use App\Core\Router;
use App\Controller\Auth\Auth;
use App\Controller\Auth\OAuth;
use App\Controller\Test;
use App\Core\CORS;
use App\Middlewares\RequireAuth;
use App\Middlewares\AlreadyLogin;
use App\Controller\Job\Job;
use App\Controller\Job\Application;
use App\Controller\Category\Category;
use App\Controller\Page;
use App\Middlewares\ParseJSON;
use App\Controller\User\Profile;

use App\Middlewares\RequireAuthView;

DB::getInstance();

$router = new Router();


CORS::handleCORS();

// Public pages
$router->add('GET', '/', [Page::class, 'home'], [[RequireAuthView::class, 'validateHome']]);
$router->add('GET', '/login', [Page::class, 'login']);
$router->add('GET', '/register', [Page::class, 'register']);

// Protected pages
$router->add(
  'GET',
  '/dashboard',
  [Page::class, 'dashboardUser'],
  [[RequireAuthView::class, 'validate']]
);

$router->add(
  'GET',
  '/admin',
  [Page::class, 'dashboardAdmin'],
  [[RequireAuthView::class, 'validateAdmin']]
);

$router->add(
  'GET',
  '/updateProfile',
  [Page::class, 'updateProfile'],
  [[RequireAuthView::class, 'validate']]
);

$router->add(
  'GET',
  '/jobseed',
  [Page::class, 'jobSeed'],
  [[RequireAuthView::class, 'validateAdmin']]
);

// API ROUTES
// $router->add('GET', '/', [Test::class, "getUUID"]);


$router->add('POST', '/api/auth/register', [Auth::class, 'register']);
$router->add('POST', '/api/auth/login', [Auth::class, 'login']);
$router->add('GET',  '/api/auth/me', [Auth::class, 'me'], [[RequireAuth::class, 'validate']]);
$router->add('POST', '/api/auth/logout', [Auth::class, 'logout'], [[RequireAuth::class, 'validate']]);

$router->add('GET', '/api/auth/google/callback', [OAuth::class, 'login']);
$router->add('GET', '/api/auth/google', [OAuth::class, 'register']);

$router->add('GET', '/api/admin/stats', [Admin::class, 'stats'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('GET', '/api/admin/users', [Admin::class, 'listUsers'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('GET', '/api/admin/user/{id}', [Admin::class, 'viewUser'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('PUT', '/api/admin/user/{id}/role', [Admin::class, 'updateRole'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('DELETE', '/api/admin/user/{id}', [Admin::class, 'deleteUser'], [[RequireAuth::class, 'validateAdmin']]);

// $router->add('GET', '/api/jobs/test', [Job::class, 'run']);
$router->add('POST', '/api/jobs/populate', [Job::class, 'seedJobs'], [[ParseJSON::class, 'parse'], [RequireAuth::class, 'validateAdmin']]);
$router->add('GET', '/api/jobs/page/{id}', [Job::class, 'index']);
$router->add('POST', '/api/jobs/create', [Job::class, 'addJobManual'], [[RequireAuth::class, 'validateAdmin'], [ParseJSON::class, 'parse']]);
$router->add('GET', '/api/jobs/show/{id}', [Job::class, 'show']);
$router->add('GET', '/api/jobs/search/{search}/{page}', [Job::class, 'searchPaginated']);
$router->add('PUT', '/api/jobs/update/{id}', [Job::class, 'editJob'], [[ParseJSON::class, 'parse'], [RequireAuth::class, 'validateAdmin']]);
$router->add('DELETE', '/api/jobs/delete/{id}', [Job::class, 'deleteJob'], [[RequireAuth::class, 'validateAdmin']]);

$router->add('POST', '/api/jobs/apply/{id}', [Application::class, 'apply'], [[RequireAuth::class, 'validate']]);
$router->add('GET', '/api/applications', [Application::class, 'index'], [[RequireAuth::class, 'validate']]);
$router->add('GET', '/api/applications/latest', [Application::class, 'latest'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('GET', '/api/applications/count', [Application::class, 'count'], [[RequireAuth::class, 'validate']]);
$router->add('GET', '/api/applications/{id}', [Application::class, 'show'], [[RequireAuth::class, 'validate']]);

// $router->add('GET', '/api/category/all', [Category::class, 'getAll']);
// $router->add('GET', '/api/category/{category}/{page}', [Category::class, 'searchPaginated']);

$router->add('GET', '/api/user/profile', [Profile::class, 'show'], [[RequireAuth::class, 'validate']]);
$router->add('DELETE', '/api/user/delete/{id}', [Profile::class, 'delete'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('PUT', '/api/user/promote/{id}', [Profile::class, 'promote'], [[RequireAuth::class, 'validateAdmin']]);
$router->add('POST', '/api/profile/avatar', [Profile::class, 'updateAvatar'], [[RequireAuth::class, 'validate']]);
$router->add('PUT', '/api/profile/placeholder', [Profile::class, 'updatePlaceholder'], [[ParseJSON::class, 'parse'], [RequireAuth::class, 'validate']]);
$router->add('PUT', '/api/profile/profile', [Profile::class, 'updateBio'], [[ParseJSON::class, 'parse'], [RequireAuth::class, 'validate']]);
$router->add('POST', '/api/profile/job-history', [Profile::class, 'addJobHistory'], [[ParseJSON::class, 'parse'], [RequireAuth::class, 'validate']]);


// ----------------- ROUTER RESOLVE -----------------
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->resolve($path, $_SERVER['REQUEST_METHOD']);
