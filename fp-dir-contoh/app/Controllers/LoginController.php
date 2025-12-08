<?php

declare(strict_types=1);
namespace App\Controllers;

use App\View;

class LoginController
{
    public function index(): View
    {
        return View::make('login/index');
    }
    
    public function login()
    {
        if(isset($_COOKIE['remember']) && ($_COOKIE['remember'] === 'login'))
        {
            $_SESSION['login'] = $_COOKIE['remember'];
            header('Location: /');
        }
        
        if(isset($_POST['login'])){
            if($_POST['login'] !== '123'){
                header('Location: /login');
                exit;
            }
            $_SESSION['login'] = $_POST['login'];
        }
        
        if(isset($_POST['remember']))
        {
            setcookie(
                'login',
                'login',
                strtotime('+10 seconds')
                );
        }
        
        // TODO: Improve Login System and add Database Support and OpenSSL
        
        header('Location: /');
    }
    
    public function logout()
    {
        unset($_SESSION['login']);
        
        header('Location: /login');
    }
}