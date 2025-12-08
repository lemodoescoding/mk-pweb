<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../app/bootstrap.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

define('STORAGE_PATH', __DIR__ . '/../storage');
define('VIEW_PATH', __DIR__ . '/../views');

use App\Config;
use App\Router;
use App\View;
use App\App;

$router = new Router();

$router
    ->add('GET', '/', [\App\Controllers\HomeController::class, 'index'])
    ->add('GET', '/date', [\App\Controllers\DateController::class, 'date'])
    ->add('GET', '/invoices', [\App\Controllers\InvoiceController::class, 'index'])
    ->add('GET', '/invoices/create', [\App\Controllers\InvoiceController::class, 'create'])
    ->add('POST', '/invoices/create', [\App\Controllers\InvoiceController::class, 'store'])
    ->add('GET', '/login', [\App\Controllers\LoginController::class, 'index'])
    ->add('POST', '/login', [\App\Controllers\LoginController::class, 'login'])
    ->add('GET', '/logout', [\App\Controllers\LoginController::class, 'logout'])
    ->add('GET', '/upload', [\App\Controllers\UploadController::class, 'index'])
    ->add('POST', '/upload', [\App\Controllers\UploadController::class, 'process'])
    ;

$request = [
        'uri' => $_SERVER['REQUEST_URI'],
        'method' => $_SERVER['REQUEST_METHOD']
    ];

$config = new Config($_ENV);

var_dump($_GET);

(new App($router, $request, $config))->run();