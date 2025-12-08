<?php

declare(strict_types=1);

session_start();

if (php_sapi_name() === 'cli-server') {
  $path = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

  if (is_file($path)) {
    return false;
  }
}

require_once __DIR__ . '/../app/bootstrap.php';

use App\Core\Router;
use App\Controller\Auth;
use App\Controller\Test;
use App\Core\CORS;

$router = new Router();

$router->add('GET', '/', [Test::class, "getUUID"]);

// $router->add('GET', '/api/auth/google', [Auth::class, "googleLogin"]);
// $router->add('GET', '/api/auth/google/callback', [Auth::class, "callback"]);

$router->add('POST', '/api/auth/register', [Auth::class, 'register']);
$router->add('POST', '/api/auth/login', [Auth::class, 'login']);
$router->add('GET',  '/api/auth/me', [Auth::class, 'me']);
$router->add('POST', '/api/auth/logout', [Auth::class, 'logout']);

CORS::handleCORS();

$router->resolve(
  parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
  ),
  $_SERVER['REQUEST_METHOD']
);
