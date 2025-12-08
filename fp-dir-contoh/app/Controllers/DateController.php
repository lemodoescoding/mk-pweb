<?php

declare(strict_types=1);
namespace App\Controllers;

use Carbon\Carbon;
use App\View;
use DateTime;

if(!isset($_SESSION['login']))
{
    header('Location: /login');
    exit;
}

class DateController
{
    public function date()
    {
        $timezone = $_GET['tz'] ?? 'UTC';
        
        try {
            $date = Carbon::now($timezone);
        } catch (\Carbon\Exceptions\InvalidTimeZoneException $e){
            echo $e->getMessage();
        } catch (\Carbon\Exceptions\InvalidFormatException $e){
            echo $e->getMessage();
        } finally {
            $date = $date ?? null;
        }
        
        if(is_null($date))
        {
            exit;
            return View::make('error/404');
        }
        
        echo $date . ' - ' . $date->tzName;
    }
}