<?php

declare(strict_types=1);
namespace App\Controllers;

use App\View;

if(!isset($_SESSION['login']))
{
    header('Location: /login');
    exit;
}

class InvoiceController
{
    public function index(): View
    {
        return View::make('invoices/index');
    }
    
    public function create(): View
    {
        return View::make('invoices/create');;
    }
    
    public function store()
    {
        return $_POST['amount'];
    }
}