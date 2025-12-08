<?php

declare(strict_types=1);
namespace App\Controllers;

use App\App;
use App\View;
use App\Models\Invoice;
use App\Models\User;
use App\Models\SignUp;
use App\Model;
use PDO;

if(!isset($_SESSION['login']))
{
    header('Location: /login');
    exit;
}

class HomeController
{
    public function index(): View
    {
        return View::make('index');
    }
    /*
    public function download()
    {
        header('Content-Type: image/png');
        header('Content-Disposition: attachment;filename="myImage.png"');
        
        readfile(STORAGE_PATH . '/if.png');
    }
    */
}